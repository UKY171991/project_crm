<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

class LeadsManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $company_name, $contact_name, $phone, $address;
    public $client_id_to_edit;
    public $isEditMode = false;
    public $showModal = false;
    public $showFeedbackModal = false;
    public $searchTerm = '';
    public $feedbackText = '';
    public $feedbackStatus = '';
    public $nextSchedule = '';
    public $selectedClientId;
    public $clientFeedbacks = [];


    protected $rules = [
        'company_name' => 'required|string|max:255',
        'contact_name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string',
    ];

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Client::with(['user', 'feedbacks.creator'])->doesntHave('projects');


        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('company_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $this->searchTerm . '%')
                  ->orWhereHas('user', function($u) {
                      $u->where('name', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
                  });
            });
        }

        $clients = $query->orderByRaw('last_whatsapp_at IS NOT NULL ASC')
            ->orderBy('last_whatsapp_at', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
            
        return view('livewire.leads-manager', [
            'clients' => $clients
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        $this->client_id_to_edit = $id;
        $this->company_name = $client->company_name;
        $this->contact_name = $client->user->name;
        $this->phone = $client->phone;
        $this->address = $client->address;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        try {
            $this->validate(array_merge($this->rules, [
                'phone' => 'required|string|max:20|unique:clients,phone',
            ]));

            $clientRole = Role::where('slug', 'client')->firstOrFail();

            $user = User::create([
                'name' => $this->contact_name,
                'email' => 'lead_' . time() . '_' . \Illuminate\Support\Str::random(5) . '@crm.local',
                'password' => Hash::make(\Illuminate\Support\Str::random(12)),
                'role_id' => $clientRole->id,
                'created_by' => auth()->id(),
            ]);

            Client::create([
                'user_id' => $user->id,
                'company_name' => $this->company_name,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => 'active',
            ]);

            session()->flash('success', 'Lead Created Successfully.');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lead store failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            session()->flash('error', 'Failed to create lead: ' . $e->getMessage());
        }
    }

    public function update()
    {
        try {
            $this->validate(array_merge($this->rules, [
                 'phone' => 'required|string|max:20|unique:clients,phone,' . $this->client_id_to_edit,
            ]));

            $client = Client::findOrFail($this->client_id_to_edit);
            
            $client->update([
                'company_name' => $this->company_name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);

            $userData = [
                'name' => $this->contact_name,
            ];

            $client->user->update($userData);

            session()->flash('success', 'Lead Updated Successfully.');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lead update failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            session()->flash('error', 'Failed to update lead: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $client = Client::findOrFail($id);
        
        if ($client->user) {
            $client->user->delete();
        }
        
        $client->delete();
        
        session()->flash('success', 'Lead Deleted Successfully.');
    }

    public function convertToClient($clientId)
    {
        $client = Client::findOrFail($clientId);
        
        // Create a sample project for this client to convert them from non-client to client
        $project = new Project([
            'title' => 'Initial Project - ' . $client->company_name,
            'description' => 'Default project created when converting lead to client',
            'status' => 'Pending',
            'budget' => 0,
            'currency' => 'USD',
            'created_by' => auth()->id(),
            'client_id' => $client->id,
        ]);
        
        $project->save();
        
        session()->flash('success', 'Lead converted to client successfully!');
    }

    public function logWhatsApp($clientId)
    {
        $client = Client::findOrFail($clientId);
        $client->update(['last_whatsapp_at' => now()]);
    }

    public function sendProposal($clientId)
    {
        $client = Client::findOrFail($clientId);
        if (!$client->phone) {
            session()->flash('error', 'Client phone number is missing.');
            return;
        }

        $clientName = $client->company_name ?? ($client->user->name ?? 'there');

        if (config('services.whatsapp.enabled')) {
            $whatsappService = new \App\Services\WhatsAppService();
            $success = $whatsappService->sendProposalMessage($client->phone, $clientName);
            
            if ($success) {
                $this->logWhatsApp($clientId);
                session()->flash('success', 'Proposal sent successfully via WhatsApp API!');
                return;
            } else {
                session()->flash('error', 'Failed to send proposal via WhatsApp API. Trying manual option...');
            }
        }

        $message = "Hi " . $clientName . "! This is Umakant Yadav.\n\nI help businesses and professionals build stunning, modern websites at the lowest budget possible. Whether you need a simple portfolio, a business site, or an e-commerce store, we deliver premium quality without the heavy price tag.\n\nLet me know if you'd be open to a quick chat to see some of our work or get a free quote!";

        $phone = preg_replace('/[^0-9]/', '', $client->phone);
        if (strlen($phone) == 10) $phone = '91' . $phone;
        $waUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
        
        $this->logWhatsApp($clientId);
        
        // Use frontend event to open WhatsApp link (allows user to manually attach the image)
        $this->dispatch('open-wa-link', url: $waUrl);
        
        session()->flash('success', 'Opening WhatsApp... The Proposal Image has been copied to your clipboard. Just press Ctrl+V to attach it!');
    }

    public function openFeedbackModal($clientId)
    {
        $this->selectedClientId = $clientId;
        $client = Client::with('feedbacks.creator')->findOrFail($clientId);
        $this->clientFeedbacks = $client->feedbacks->toArray();
        $this->feedbackText = '';
        $this->feedbackStatus = 'Connected'; // Default status
        $this->nextSchedule = '';
        $this->showFeedbackModal = true;
    }

    public function saveFeedback()
    {
        $this->validate([
            'feedbackText' => 'required|min:3',
            'feedbackStatus' => 'required',
            'nextSchedule' => 'nullable|date'
        ]);

        \App\Models\ClientFeedback::create([
            'client_id' => $this->selectedClientId,
            'feedback' => $this->feedbackText,
            'status' => $this->feedbackStatus,
            'next_schedule' => $this->nextSchedule ?: null,
            'created_by' => auth()->id()
        ]);

        $this->feedbackText = '';
        $this->openFeedbackModal($this->selectedClientId); // Refresh list
        session()->flash('success', 'Feedback added successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showFeedbackModal = false;
    }


    private function resetInputFields()
    {
        $this->company_name = '';
        $this->contact_name = '';
        $this->phone = '';
        $this->address = '';
        $this->client_id_to_edit = null;
        $this->feedbackText = '';
        $this->feedbackStatus = '';
        $this->nextSchedule = '';
        $this->selectedClientId = null;
        $this->clientFeedbacks = [];
    }

}
