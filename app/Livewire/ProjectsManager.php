<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Client;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;

class ProjectsManager extends Component
{
    public $projects;
    public $title, $description, $budget, $currency = 'USD', $start_date, $due_date, $client_id, $project_id_to_edit, $status;
    public $isEditMode = false;
    public $showModal = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'budget' => 'nullable|numeric|min:0',
        'currency' => 'required|string|max:10',
        'start_date' => 'nullable|date',
        'due_date' => 'nullable|date',
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

        $this->projects = $query->with('client', 'mediaFiles', 'payments')->latest()->get();
        $clients = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $clients = Client::with('user')->get();
        }

        $activeCurrencies = Currency::where('is_active', true)->get();

        return view('livewire.projects-manager', compact('clients', 'activeCurrencies'));
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
        
        // Authorization check
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
        $this->budget = $project->budget;
        $this->currency = $project->currency ?? 'USD';
        $this->start_date = $project->start_date;
        $this->due_date = $project->end_date;
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
            'budget' => $this->budget ?: 0,
            'currency' => $this->currency,
            'start_date' => $this->start_date,
            'end_date' => $this->due_date,
        ]);
        $project->created_by = $user->id;
        $project->status = 'Pending';

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
        
        $project->update([
            'title' => $this->title,
            'description' => $this->description,
            'budget' => $this->budget ?: 0,
            'currency' => $this->currency,
            'start_date' => $this->start_date,
            'end_date' => $this->due_date,
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
        
        $user = Auth::user();
        if ($user->hasRole('client')) {
            abort(403, 'Clients cannot delete projects.');
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
        $this->budget = '';
        // Set default currency to first active if available
        $defaultCurrency = Currency::where('is_active', true)->first();
        $this->currency = $defaultCurrency ? $defaultCurrency->code : 'USD';
        $this->start_date = '';
        $this->due_date = '';
        $this->client_id = '';
        $this->status = '';
        $this->project_id_to_edit = null;
    }
}
