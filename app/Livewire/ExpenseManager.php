<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;

class ExpenseManager extends Component
{
    public $expenses;
    public $amount, $currency = 'USD', $expense_date, $description, $category, $project_id, $expense_id_to_edit;
    public $isEditMode = false;
    public $showModal = false;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'currency' => 'required|string|max:10',
        'expense_date' => 'required|date',
        'description' => 'required|string|max:255',
        'category' => 'nullable|string|max:255',
        'project_id' => 'nullable|exists:projects,id',
    ];

    public function render()
    {
        $user = Auth::user();
        $query = Expense::with(['project', 'user']);

        if ($user->hasRole('master') || $user->hasRole('admin')) {
             // See all
        } else {
            // See only own expenses? or expenses of projects they are assigned to?
            // For now, let's assume see only own expenses
            $query->where('user_id', $user->id);
        }

        $this->expenses = $query->orderBy('expense_date', 'desc')->get();
        
        $projects = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $projects = Project::latest()->get();
        } else {
             $projects = Project::whereHas('assignees', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
        }

        $activeCurrencies = Currency::where('is_active', true)->get();

        return view('livewire.expense-manager', compact('projects', 'activeCurrencies'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->expense_date = date('Y-m-d');
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->isEditMode = true;
        $expense = Expense::findOrFail($id);
        $this->expense_id_to_edit = $id;
        $this->amount = $expense->amount;
        $this->currency = $expense->currency ?? 'USD';
        $this->expense_date = $expense->expense_date ? $expense->expense_date->format('Y-m-d') : date('Y-m-d');
        $this->description = $expense->description;
        $this->category = $expense->category;
        $this->project_id = $expense->project_id;
        
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        Expense::create([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'expense_date' => $this->expense_date,
            'description' => $this->description,
            'category' => $this->category,
            'project_id' => $this->project_id ?: null,
            'user_id' => Auth::id(),
        ]);

        session()->flash('success', 'Expense recorded successfully.');
        $this->closeModal();
    }

    public function update()
    {
        $this->validate();

        $expense = Expense::findOrFail($this->expense_id_to_edit);
        $expense->update([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'expense_date' => $this->expense_date,
            'description' => $this->description,
            'category' => $this->category,
            'project_id' => $this->project_id ?: null,
        ]);

        session()->flash('success', 'Expense updated successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        Expense::destroy($id);
        session()->flash('success', 'Expense deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetInputFields()
    {
        $this->amount = '';
        $defaultCurrency = Currency::where('is_active', true)->first();
        $this->currency = $defaultCurrency ? $defaultCurrency->code : 'USD';
        $this->expense_date = '';
        $this->description = '';
        $this->category = '';
        $this->project_id = '';
        $this->expense_id_to_edit = null;
    }
}
