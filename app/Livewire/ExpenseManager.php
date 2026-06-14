<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExpenseManager extends Component
{
    public $expenses;
    public $amount, $currency = 'USD', $expense_date, $description, $category, $project_id, $expense_id_to_edit, $status = 'Paid';
    public $isEditMode = false;
    public $showModal = false;
    public $selectedMonth = '';
    public $totalAmount = 0;
    public $paidAmount = 0;
    public $pendingAmount = 0;
    public $rejectedAmount = 0;
    public $overallPendingAmount = 0;
    public $thisMonthPendingAmount = 0;

    public function mount()
    {
        // Set default to current month
        $this->selectedMonth = date('Y-m');
    }

    public function updatingSelectedMonth()
    {
        // Triggers re-render when selectedMonth changes
    }

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'currency' => 'required|string|max:10',
        'expense_date' => 'required|date',
        'description' => 'required|string|max:255',
        'category' => 'nullable|string|max:255',
        'project_id' => 'nullable|exists:projects,id',
        'status' => 'required|in:Paid,Pending,Rejected',
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

        // Apply month filter if selected
        if ($this->selectedMonth) {
            $query->whereMonth('expense_date', '=', Carbon::parse($this->selectedMonth)->month)
                  ->whereYear('expense_date', '=', Carbon::parse($this->selectedMonth)->year);
        }

        $this->expenses = $query->orderByRaw("CASE 
                                       WHEN status = 'Pending' THEN 1 
                                       WHEN status = 'Rejected' THEN 2 
                                       WHEN status = 'Paid' THEN 3 
                                       ELSE 4 
                                      END ASC")
                           ->orderBy('expense_date', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->get();
        
        // Calculate totals based on filtered expenses
        $this->calculateTotals();

        // Calculate overall pending (all time) and this month pending
        $pendingQuery = Expense::where('status', 'Pending');
        if (!($user->hasRole('master') || $user->hasRole('admin'))) {
            $pendingQuery->where('user_id', $user->id);
        }
        $this->overallPendingAmount = (clone $pendingQuery)->sum('amount');
        $thisMonthPending = (clone $pendingQuery)
            ->whereMonth('expense_date', '=', Carbon::now()->month)
            ->whereYear('expense_date', '=', Carbon::now()->year)
            ->sum('amount');
        $this->thisMonthPendingAmount = $thisMonthPending;
        
        $projects = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $projects = Project::latest()->get();
        } else {
             $projects = Project::whereHas('assignees', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
        }

        $activeCurrencies = Currency::where('is_active', true)->get();
        
        // Generate all 12 months of the current year
        $availableMonths = collect();
        $currentYear = (int) date('Y');
        for ($month = 12; $month >= 1; $month--) {
            $availableMonths->push(sprintf('%04d-%02d', $currentYear, $month));
        }

        return view('livewire.expense-manager', compact('projects', 'activeCurrencies', 'availableMonths'));
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
        $this->status = $expense->status ?? 'Paid';
        
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
            'status' => $this->status,
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
            'status' => $this->status,
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
        $this->status = 'Paid';
        $this->expense_id_to_edit = null;
    }
    
    public function updatedSelectedMonth()
    {
        // Triggers re-render and recalculation automatically
    }
    
    private function calculateTotals()
    {
        $this->totalAmount = 0;
        $this->paidAmount = 0;
        $this->pendingAmount = 0;
        $this->rejectedAmount = 0;
        
        foreach ($this->expenses as $expense) {
            $this->totalAmount += $expense->amount;
            
            switch ($expense->status) {
                case 'Paid':
                    $this->paidAmount += $expense->amount;
                    break;
                case 'Pending':
                    $this->pendingAmount += $expense->amount;
                    break;
                case 'Rejected':
                    $this->rejectedAmount += $expense->amount;
                    break;
            }
        }
    }
}
