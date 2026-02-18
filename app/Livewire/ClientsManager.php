<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ClientsManager extends Component
{
    public $clients;
    public $company_name, $contact_name, $email, $password, $phone, $address;
    public $client_id_to_edit;
    public $isEditMode = false;
    public $showModal = false;

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'contact_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
    ];

    public function render()
    {
        $this->clients = Client::with('user')
            ->withCount(['projects as pending_tasks_count' => function ($query) {
                $query->whereIn('status', ['Pending', 'Running']);
            }])
            ->withCount(['projects as completed_tasks_count' => function ($query) {
                $query->where('status', 'Completed');
            }])
            ->with(['projects.payments' => function($query) {
                $query->where('payment_status', 'Paid');
            }])
            ->orderBy('pending_tasks_count', 'desc')
            ->get();
            
        return view('livewire.clients-manager');
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
        $this->email = $client->user->email;
        $this->phone = $client->phone;
        $this->address = $client->address;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate(array_merge($this->rules, [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]));

        $clientRole = Role::where('slug', 'client')->firstOrFail();

        $user = User::create([
            'name' => $this->contact_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
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

        session()->flash('success', 'Client Created Successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function update()
    {
        $this->validate(array_merge($this->rules, [
             'email' => 'required|email', // complex rule needed for ignore
        ]));

        $client = Client::findOrFail($this->client_id_to_edit);
        
        $client->update([
            'company_name' => $this->company_name,
            'phone' => $this->phone,
            'address' => $this->address,
        ]);

        $userData = [
            'name' => $this->contact_name,
            'email' => $this->email,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $client->user->update($userData);

        session()->flash('success', 'Client Updated Successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        $client = Client::findOrFail($id);
        
        // Delete the associated user if exists
        if ($client->user) {
            $client->user->delete();
        }
        
        // Delete the client (this will also handle cascading)
        $client->delete();
        
        session()->flash('success', 'Client Deleted Successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetInputFields()
    {
        $this->company_name = '';
        $this->contact_name = '';
        $this->email = '';
        $this->password = '';
        $this->phone = '';
        $this->address = '';
        $this->client_id_to_edit = null;
    }
}
