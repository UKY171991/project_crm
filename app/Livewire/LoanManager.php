<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Loan;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanManager extends Component
{
    public $loans, $parentLoanList;
    public $user_id, $amount, $loan_date, $description, $loan_id_to_edit, $loan_type, $parent_id;
    public $isEditMode = false;
    public $showModal = false;
    public $totalBalance = 0;
    public $totalLoan = 0;
    public $totalEMI = 0;
    public $recordAsExpense = false;
    public $transactionType = 'loan'; // loan or emi
    public $activeTab = 'all'; // all, loans, emis

    public function mount()
    {
        $this->loan_date = date('Y-m-d');
    }

    protected $rules = [
        'amount' => 'required|numeric',
        'loan_date' => 'required|date',
        'description' => 'nullable|string|max:255',
        'loan_type' => 'nullable|string|max:255',
    ];

    public function render()
    {
        $query = Loan::query();

        if ($this->activeTab == 'loans') {
            $query->where('amount', '>', 0);
        } elseif ($this->activeTab == 'emis') {
            $query->where('amount', '<', 0);
        }

        $this->loans = $query->orderBy('loan_date', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->get();
        
        $this->totalLoan = Loan::where('amount', '>', 0)->sum('amount');
        $this->totalEMI = abs(Loan::where('amount', '<', 0)->sum('amount'));
        $this->totalBalance = $this->totalLoan - $this->totalEMI;

        $this->parentLoanList = Loan::where('amount', '>', 0)->orderBy('loan_date', 'desc')->get();

        $loans = $this->loans;
        $totalLoan = $this->totalLoan;
        $totalEMI = $this->totalEMI;
        $totalBalance = $this->totalBalance;

        return view('livewire.loan-manager', compact('loans', 'totalLoan', 'totalEMI', 'totalBalance'));
    }

    public function create($type = 'loan')
    {
        $this->resetInputFields();
        $this->transactionType = $type;
        $this->loan_date = date('Y-m-d');
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->isEditMode = true;
        $loan = Loan::findOrFail($id);
        $this->loan_id_to_edit = $id;
        $this->user_id = $loan->user_id;
        $this->amount = $loan->amount;
        $this->loan_type = $loan->loan_type;
        $this->parent_id = $loan->parent_id;
        $this->loan_date = $loan->loan_date->format('Y-m-d');
        $this->description = $loan->description;
        
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        $finalAmount = ($this->transactionType == 'emi') ? -abs($this->amount) : abs($this->amount);

        $loan = Loan::create([
            'user_id' => Auth::id(),
            'parent_id' => ($this->transactionType == 'emi') ? $this->parent_id : null,
            'amount' => $finalAmount,
            'loan_type' => $this->loan_type,
            'loan_date' => $this->loan_date,
            'description' => $this->description,
            'created_by' => Auth::id(),
        ]);

        if ($this->recordAsExpense && $finalAmount > 0) {
            Expense::create([
                'amount' => abs($finalAmount),
                'currency' => 'USD', 
                'expense_date' => $this->loan_date,
                'description' => ($this->loan_type ?: 'Loan') . ': ' . ($this->description ?: 'User ID ' . Auth::id()),
                'category' => $this->loan_type ?: 'Loan',
                'user_id' => Auth::id(),
                'status' => 'Paid',
            ]);
        }

        session()->flash('success', 'Loan transaction recorded successfully.');
        $this->closeModal();
    }

    public function update()
    {
        $this->validate();

        $loan = Loan::findOrFail($this->loan_id_to_edit);
        $loan->update([
            'amount' => $this->amount,
            'parent_id' => $this->parent_id,
            'loan_type' => $this->loan_type,
            'loan_date' => $this->loan_date,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Loan transaction updated successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        Loan::destroy($id);
        session()->flash('success', 'Loan transaction deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetInputFields()
    {
        $this->user_id = '';
        $this->amount = '';
        $this->loan_type = '';
        $this->parent_id = '';
        $this->loan_date = date('Y-m-d');
        $this->description = '';
        $this->loan_id_to_edit = null;
        $this->recordAsExpense = false;
    }
}
