<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ProjectsManager extends Component
{
    public $projects;
    public $title, $description, $start_date, $client_id, $project_id_to_edit, $status;
    public $isEditMode = false;
    public $showModal = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_date' => 'nullable|date',
    ];

    public function render()
    {
        $user = Auth::user();
        $query = Project::query();

        if ($user->hasRole('master')) {
            // See all
        } elseif ($user->hasRole('admin')) {
            $query->where('created_by', $user->id);
        } elseif ($user->hasRole('client')) {
            $query->where('client_id', $user->clientProfile->id);
        } else {
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $this->projects = $query->with('client', 'mediaFiles')->latest()->get();
        $clients = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $clients = Client::with('user')->get();
        }

        return view('livewire.projects-manager', compact('clients'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        
        // Authorization check (simplified for now, can be robust)
        $user = Auth::user();
        if ($user->hasRole('admin') && $project->created_by != $user->id) {
            abort(403);
        }
        if ($user->hasRole('client') && $project->client_id != $user->clientProfile->id) {
            abort(403);
        }

        $this->project_id_to_edit = $id;
        $this->title = $project->title;
        $this->description = $project->description;
        $this->start_date = $project->start_date;
        $this->client_id = $project->client_id;
        $this->status = $project->status;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $user = Auth::user();
        $this->validate();

        $project = new Project([
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
        ]);
        $project->created_by = $user->id;
        $project->status = 'Pending'; // Default

        if ($user->hasRole('client')) {
            $project->client_id = $user->clientProfile->id;
        } else {
             $this->validate(['client_id' => 'required|exists:clients,id']);
             $project->client_id = $this->client_id;
        }

        $project->save();

        session()->flash('success', 'Project Created Successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function update()
    {
        $this->validate();
        
        $project = Project::findOrFail($this->project_id_to_edit);
        
        // Authorization check (simplified)
        // ...

        $project->update([
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'status' => $this->status ?? $project->status,
        ]);

        if (Auth::user()->hasRole('master') || Auth::user()->hasRole('admin')) {
             if($this->client_id) {
                 $project->client_id = $this->client_id;
                 $project->save();
             }
        }

        session()->flash('success', 'Project Updated Successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        $project = Project::findOrFail($id);
        
        // Authorization
        $user = Auth::user();
        if ($user->hasRole('client')) {
            abort(403, 'Clients cannot delete projects.'); // Usually logic
        }

        $project->delete();
        session()->flash('success', 'Project Deleted Successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetInputFields()
    {
        $this->title = '';
        $this->description = '';
        $this->start_date = '';
        $this->client_id = '';
        $this->status = '';
        $this->project_id_to_edit = null;
    }
}
