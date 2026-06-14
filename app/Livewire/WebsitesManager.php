<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Website;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class WebsitesManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchTerm = '';
    public $filterClient = '';
    public $isEditMode = false;
    public $showModal = false;
    public $activeTab = 'details'; // details, history

    // Form fields for Website
    public $website_id;

    // Form fields for Renewal
    public $renewal_type = 'domain';
    public $renewal_date;
    public $renewal_new_expiry_date;
    public $renewal_amount;
    public $renewal_currency = 'INR';
    public $renewal_payment_status = 'Paid';
    public $renewal_notes;

    public $showRenewalForm = false;
    public $client_id;
    public $name;
    public $url;
    public $domain_name;
    public $domain_expiry_date;
    public $ssl_expiry_date;
    public $hosting_provider;
    public $hosting_expiry_date;
    public $server_ip;
    public $php_version;
    public $cms;
    public $admin_url;
    public $admin_username;
    public $admin_password;
    public $notes;
    public $is_active = true;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'name' => 'required|string|max:255',
        'url' => 'nullable|url|max:255',
        'domain_name' => 'nullable|string|max:255',
        'domain_expiry_date' => 'nullable|date',
        'ssl_expiry_date' => 'nullable|date',
        'hosting_provider' => 'nullable|string|max:255',
        'hosting_expiry_date' => 'nullable|date',
        'server_ip' => 'nullable|string|max:45',
        'php_version' => 'nullable|string|max:20',
        'cms' => 'nullable|string|max:50',
        'admin_url' => 'nullable|url|max:255',
        'admin_username' => 'nullable|string|max:100',
        'admin_password' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $user = Auth::user();
        $query = Website::query();

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('domain_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('url', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->filterClient) {
            $query->where('client_id', $this->filterClient);
        }

        if ($user->hasRole('client')) {
            $query->where('client_id', $user->clientProfile->id ?? 0);
        }

        $websites = $query->with('client')->latest()->paginate(10);
        
        // Only show actual clients (those with projects) in the dropdown, excluding leads (non-clients)
        $clients = Client::has('projects')->orderBy('company_name')->get();

        return view('livewire.websites-manager', [
            'websites' => $websites,
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function resetInputFields()
    {
        $this->website_id = null;
        $this->client_id = '';
        $this->name = '';
        $this->url = '';
        $this->domain_name = '';
        $this->domain_expiry_date = '';
        $this->ssl_expiry_date = '';
        $this->hosting_provider = '';
        $this->hosting_expiry_date = '';
        $this->server_ip = '';
        $this->php_version = '';
        $this->cms = '';
        $this->admin_url = '';
        $this->admin_username = '';
        $this->admin_password = '';
        $this->notes = '';
        $this->is_active = true;
    }

    public function store()
    {
        $validatedData = $this->validate();
        Website::create($validatedData);
        session()->flash('success', 'Website added successfully.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $website = Website::findOrFail($id);
        $this->website_id = $id;
        $this->client_id = $website->client_id;
        $this->name = $website->name;
        $this->url = $website->url;
        $this->domain_name = $website->domain_name;
        $this->domain_expiry_date = $website->domain_expiry_date ? $website->domain_expiry_date->format('Y-m-d') : '';
        $this->ssl_expiry_date = $website->ssl_expiry_date ? $website->ssl_expiry_date->format('Y-m-d') : '';
        $this->hosting_provider = $website->hosting_provider;
        $this->hosting_expiry_date = $website->hosting_expiry_date ? $website->hosting_expiry_date->format('Y-m-d') : '';
        $this->server_ip = $website->server_ip;
        $this->php_version = $website->php_version;
        $this->cms = $website->cms;
        $this->admin_url = $website->admin_url;
        $this->admin_username = $website->admin_username;
        $this->admin_password = $website->admin_password;
        $this->notes = $website->notes;
        $this->is_active = $website->is_active;

        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        $validatedData = $this->validate();
        $website = Website::findOrFail($this->website_id);
        $website->update($validatedData);
        session()->flash('success', 'Website updated successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        Website::find($id)->delete();
        session()->flash('success', 'Website deleted successfully.');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleRenewalForm()
    {
        $this->showRenewalForm = !$this->showRenewalForm;
        if ($this->showRenewalForm) {
            $this->renewal_date = now()->format('Y-m-d');
            $this->renewal_currency = 'INR';
            $this->renewal_payment_status = 'Paid';
        }
    }

    public function storeRenewal()
    {
        $this->validate([
            'renewal_type' => 'required|string',
            'renewal_date' => 'required|date',
            'renewal_new_expiry_date' => 'required|date',
            'renewal_amount' => 'required|numeric',
            'renewal_payment_status' => 'required|string',
        ]);

        \App\Models\WebsiteRenewal::create([
            'website_id' => $this->website_id,
            'type' => $this->renewal_type,
            'renewal_date' => $this->renewal_date,
            'new_expiry_date' => $this->renewal_new_expiry_date,
            'amount' => $this->renewal_amount,
            'currency' => $this->renewal_currency,
            'payment_status' => $this->renewal_payment_status,
            'notes' => $this->renewal_notes,
        ]);

        // Update the website's expiry date based on type
        $website = Website::find($this->website_id);
        if ($this->renewal_type == 'domain') {
            $website->domain_expiry_date = $this->renewal_new_expiry_date;
        } elseif ($this->renewal_type == 'hosting') {
            $website->hosting_expiry_date = $this->renewal_new_expiry_date;
        } elseif ($this->renewal_type == 'ssl') {
            $website->ssl_expiry_date = $this->renewal_new_expiry_date;
        }
        $website->save();

        $this->showRenewalForm = false;
        $this->resetRenewalFields();
        session()->flash('success', 'Renewal history added successfully.');
    }

    public function resetRenewalFields()
    {
        $this->renewal_type = 'domain';
        $this->renewal_date = '';
        $this->renewal_new_expiry_date = '';
        $this->renewal_amount = '';
        $this->renewal_notes = '';
    }

    public function deleteRenewal($id)
    {
        \App\Models\WebsiteRenewal::find($id)->delete();
        session()->flash('success', 'Renewal record deleted.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->activeTab = 'details';
        $this->resetInputFields();
        $this->showRenewalForm = false;
    }
}
