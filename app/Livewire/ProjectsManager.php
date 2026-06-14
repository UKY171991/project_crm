<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\Client;
use App\Models\Currency;
use App\Models\ProjectRemark;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsAppService;

class ProjectsManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $searchTerm = '';
    public $filterStatus = '';
    public $filterClient = '';

    public $title, $description, $remarks, $budget, $currency = 'USD', $start_date, $due_date, $client_id, $project_id_to_edit, $status;
    public $project_urls = [];
    public $reminder_frequency = 'none';
    public $reminder_enabled = false;
    public $isEditMode = false;
    public $showModal = false;
    protected $whatsappService;

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterClient()
    {
        $this->resetPage();
    }

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
        
        // Initialize WhatsApp service
        $this->whatsappService = new WhatsAppService();

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

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterClient) {
            $query->where('client_id', $this->filterClient);
        }

        $projects = $query->with('client', 'mediaFiles', 'payments')
            ->orderByRaw("CASE 
                WHEN status = 'Pending' THEN 1 
                WHEN status = 'Running' THEN 2 
                WHEN status = 'Pending Payment' THEN 3
                WHEN status = 'Completed' THEN 4
                ELSE 5 
            END")
            ->latest()
            ->paginate(10);
        $clients = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            // Get only converted clients (with projects)
            $clients = Client::whereHas('user', function($q){
                $q->where('is_active', true);
            })->has('projects')->with('user')->get();
        }

        $activeCurrencies = Currency::where('is_active', true)->get();
        $defaultCurrency = $activeCurrencies->where('code', 'INR')->first() ?? $activeCurrencies->first();
        $currencySymbol = $defaultCurrency ? ($defaultCurrency->symbol ?? 'INR') : 'INR';

        // Calculate Stats
        $baseQuery = clone $query;
        $allProjects = $baseQuery->get();
        
        $stats = [
            'current_month' => ['completed' => 0, 'pending' => 0],
            'yearly' => ['completed' => 0, 'pending' => 0],
            'all_time' => ['completed' => 0, 'pending' => 0]
        ];

        $currentMonth = now()->format('Y-m');
        $currentYear = now()->format('Y');

        foreach($allProjects as $p) {
            $paid = $p->total_paid;
            $balance = $p->balance;
            
            $stats['all_time']['completed'] += $paid;
            $stats['all_time']['pending'] += $balance;

            if ($p->created_at->format('Y-m') === $currentMonth) {
                $stats['current_month']['completed'] += $paid;
                $stats['current_month']['pending'] += $balance;
            }
            if ($p->created_at->format('Y') === $currentYear) {
                $stats['yearly']['completed'] += $paid;
                $stats['yearly']['pending'] += $balance;
            }
        }

        return view('livewire.projects-manager', compact('projects', 'clients', 'activeCurrencies', 'stats', 'currencySymbol'));
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
        $this->reminder_frequency = $project->reminder_frequency ?? 'none';
        $this->reminder_enabled = (bool) $project->reminder_enabled;
        
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
            'reminder_frequency' => $this->reminder_frequency,
            'reminder_enabled' => $this->reminder_enabled,
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
            'reminder_frequency' => $this->reminder_frequency,
            'reminder_enabled' => $this->reminder_enabled,
        ]);

        // Auto-change to Pending Payment if project is completed
        $oldStatus = $project->status;
        if (($this->status ?? $project->status) == 'Completed') {
            $project->update(['status' => 'Pending Payment']);
        }
        
        // Send WhatsApp notification to client
        if ($project->client && $project->client->phone && $oldStatus !== $project->status) {
            $this->whatsappService->sendProjectStatusUpdate(
                $project->client,
                $project,
                $oldStatus,
                $project->status
            );
        }

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
        $this->reminder_frequency = 'none';
        $this->reminder_enabled = false;
        $this->project_id_to_edit = null;
    }
}
