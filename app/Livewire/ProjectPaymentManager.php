<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectPaymentManager extends Component
{
    public $project;
    public $amount, $currency = 'USD', $payment_method, $payment_status, $transaction_id, $payment_id_to_edit;
    public $showModal = false;
    public $isEditMode = false;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'currency' => 'required|string|max:10',
        'payment_method' => 'required|string|max:255',
        'payment_status' => 'required|in:Paid,Unpaid,Partial',
        'transaction_id' => 'nullable|string|max:255',
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function render()
    {
        $payments = Payment::where('project_id', $this->project->id)->latest()->get();
        return view('livewire.project-payment-manager', compact('payments'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        $this->payment_id_to_edit = $id;
        $this->amount = $payment->amount;
        $this->currency = $payment->currency ?? 'USD';
        $this->payment_method = $payment->payment_method;
        $this->payment_status = $payment->payment_status;
        $this->transaction_id = $payment->transaction_id;

        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        Payment::create([
            'project_id' => $this->project->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'transaction_id' => $this->transaction_id,
            'created_by' => Auth::id(),
        ]);

        session()->flash('payment_success', 'Payment recorded.');
        $this->closeModal();
    }

    public function update()
    {
        $this->validate();

        $payment = Payment::findOrFail($this->payment_id_to_edit);
        $payment->update([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'transaction_id' => $this->transaction_id,
        ]);

        session()->flash('payment_success', 'Payment updated.');
        $this->closeModal();
    }

    public function delete($id)
    {
        Payment::destroy($id);
        session()->flash('payment_success', 'Payment deleted.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetInputFields()
    {
        $this->amount = '';
        $this->currency = 'USD';
        $this->payment_method = 'UPI';
        $this->payment_status = 'Paid';
        $this->transaction_id = '';
        $this->payment_id_to_edit = null;
    }
}
