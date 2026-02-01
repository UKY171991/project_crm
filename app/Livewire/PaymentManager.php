<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentManager extends Component
{
    public $payments;
    public $amount, $currency = 'USD', $payment_date, $payment_method, $payment_status, $transaction_id, $project_id, $payment_id_to_edit;
    public $isEditMode = false;
    public $showModal = false;
    public $selectedProjectBudget = 0;
    public $selectedProjectBalance = 0;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'currency' => 'required|string|max:10',
        'payment_date' => 'required|date',
        'payment_method' => 'required|string|max:255',
        'payment_status' => 'required|in:Paid,Unpaid,Partial',
        'project_id' => 'required|exists:projects,id',
        'transaction_id' => 'nullable|string|max:255',
    ];

    public function updatedProjectId($value)
    {
        if ($value) {
            $project = Project::find($value);
            if ($project) {
                $this->selectedProjectBudget = $project->budget;
                $this->selectedProjectBalance = $project->balance;
            }
        } else {
            $this->selectedProjectBudget = 0;
            $this->selectedProjectBalance = 0;
        }
    }

    public function render()
    {
        $user = Auth::user();
        $query = Payment::with(['project.client', 'creator']);

        if ($user->hasRole('master')) {
            // See all
        } elseif ($user->hasRole('admin')) {
            $query->whereHas('project', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        } elseif ($user->hasRole('client')) {
            $query->whereHas('project', function($q) use ($user) {
                $q->where('client_id', $user->clientProfile->id);
            });
        } else {
            $query->whereHas('project', function($q) use ($user) {
                $q->whereHas('assignees', function($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });
        }

        $this->payments = $query->orderBy('payment_date', 'desc')->get();
        $projects = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $projects = Project::latest()->get();
        }

        $activeCurrencies = Currency::where('is_active', true)->get();

        return view('livewire.payment-manager', compact('projects', 'activeCurrencies'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->payment_date = date('Y-m-d');
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        $this->payment_id_to_edit = $id;
        $this->amount = $payment->amount;
        $this->currency = $payment->currency ?? 'USD';
        $this->payment_date = $payment->payment_date ? $payment->payment_date->format('Y-m-d') : date('Y-m-d');
        $this->payment_method = $payment->payment_method;
        $this->payment_status = $payment->payment_status;
        $this->transaction_id = $payment->transaction_id;
        $this->project_id = $payment->project_id;
        
        $this->updatedProjectId($this->project_id);

        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        Payment::create([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'transaction_id' => $this->transaction_id,
            'project_id' => $this->project_id,
            'created_by' => Auth::id(),
        ]);

        session()->flash('success', 'Payment recorded successfully.');
        $this->closeModal();
    }

    public function update()
    {
        $this->validate();

        $payment = Payment::findOrFail($this->payment_id_to_edit);
        $payment->update([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'transaction_id' => $this->transaction_id,
            'project_id' => $this->project_id,
        ]);

        session()->flash('success', 'Payment updated successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        Payment::destroy($id);
        session()->flash('success', 'Payment deleted successfully.');
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
        $this->payment_date = '';
        $this->payment_method = '';
        $this->payment_status = 'Unpaid';
        $this->transaction_id = '';
        $this->project_id = '';
        $this->payment_id_to_edit = null;
        $this->selectedProjectBudget = 0;
        $this->selectedProjectBalance = 0;
    }
}
