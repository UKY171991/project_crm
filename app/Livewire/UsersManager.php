<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersManager extends Component
{
    public $users, $roles;
    public $name, $email, $password, $role_id, $user_id_to_edit;
    public $isEditMode = false;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'role_id' => 'required|exists:roles,id',
    ];

    public function render()
    {
        $this->users = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'user']);
        })->with('role')->latest()->get();
        
        $this->roles = Role::whereIn('slug', ['admin', 'user'])->get();

        return view('livewire.users-manager');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id_to_edit = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate(array_merge($this->rules, [
            'email' => 'unique:users,email',
            'password' => 'required|min:8',
        ]));

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => $this->role_id,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'User Created Successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function update()
    {
        $this->validate();

        $user = User::findOrFail($this->user_id_to_edit);
        
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $user->update($userData);

        session()->flash('success', 'User Updated Successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        if ($id == auth()->id()) {
            session()->flash('error', 'Cannot delete self.');
            return;
        }
        User::destroy($id);
        session()->flash('success', 'User Deleted Successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role_id = '';
        $this->user_id_to_edit = null;
    }
}
