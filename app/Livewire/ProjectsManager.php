<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Client;
use App\Models\Currency;
use App\Models\ProjectRemark;
use Illuminate\Support\Facades\Auth;

class ProjectsManager extends Component
{
    public $projects;
    public $title, $description, $remarks, $budget, $currency = 'USD', $start_date, $due_date, $client_id, $project_id_to_edit, $status;
    public $project_urls = [];
    public $isEditMode = false;
    public $showModal = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'remarks' => 'nullable|string',
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

        $this->projects = $query->with('client', 'mediaFiles', 'payments')
            ->orderByRaw("CASE 
                WHEN status = 'Pending' THEN 1 
                WHEN status = 'Running' THEN 2 
                WHEN status = 'Completed' THEN 3
                ELSE 4 
            END")
            ->latest()
            ->get();
        $clients = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $clients = Client::whereHas('user', function($q){
                $q->where('is_active', true);
            })->with('user')->get();
        }

        $activeCurrencies = Currency::where('is_active', true)->get();

        return view('livewire.projects-manager', compact('clients', 'activeCurrencies'));
    }

    public function addUrl()
    {
        $this->project_urls[] = ['label' => '', 'url' => ''];
    }

    public function removeUrl($index)
    {
        unset($this->project_urls[$index]);
        $this->project_urls = array_values($this->project_urls);
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
        $this->remarks = ''; // Don't load existing remarks here, they are in a timeline now
        $this->budget = $project->budget;
        $this->currency = $project->currency ?? 'USD';
        $this->start_date = $project->start_date ? $project->start_date->format('Y-m-d') : '';
        $this->due_date = $project->end_date ? $project->end_date->format('Y-m-d') : '';
        $this->client_id = $project->client_id;
        $this->status = $project->status;
        $this->project_urls = is_array($project->urls) ? $project->urls : [];
        
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
            'urls' => array_filter($this->project_urls, fn($u) => !empty($u['url'])),
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

        // If a remark was provided during creation, add it to the remarks table
        if (!empty($this->remarks)) {
            ProjectRemark::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'remark' => $this->remarks
            ]);
        }

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
            'urls' => array_filter($this->project_urls, fn($u) => !empty($u['url'])),
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

        // If a new remark was provided during update, add it to the timeline
        if (!empty($this->remarks)) {
            ProjectRemark::create([
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'remark' => $this->remarks
            ]);
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
        $this->remarks = '';
        $this->budget = '';
        // Set default currency to first active if available
        $defaultCurrency = Currency::where('is_active', true)->first();
        $this->currency = $defaultCurrency ? $defaultCurrency->code : 'USD';
        $this->start_date = '';
        $this->due_date = '';
        $this->client_id = '';
        $this->status = '';
        $this->project_urls = [];
        $this->project_id_to_edit = null;
    }
}
