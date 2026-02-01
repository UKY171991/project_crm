<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-money-check-alt mr-1"></i> Payments</h3>
        @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
        <div class="card-tools">
            <button wire:click="create" class="btn btn-tool text-success">
                <i class="fas fa-plus"></i> Add Payment
            </button>
        </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if(session()->has('payment_success'))
            <div class="alert alert-success mx-3 mt-2 py-1 small">
                {{ session('payment_success') }}
            </div>
        @endif

        <table class="table table-sm table-valign-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amt</th>
                    <th>Status</th>
                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                    <th class="text-right">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('d/m/y') }}</td>
                    <td><strong>{{ $payment->currency }}</strong> {{ number_format($payment->amount, 2) }}</td>
                    <td>
                        <small class="badge {{ $payment->payment_status == 'Paid' ? 'badge-success' : ($payment->payment_status == 'Partial' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $payment->payment_status }}
                        </small>
                    </td>
                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                    <td class="text-right">
                        <button wire:click="edit({{ $payment->id }})" class="btn btn-xs btn-info">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">No payments recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit' : 'Record' }} Payment</h5>
                    <button type="button" class="close" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-secondary py-1 small mb-2">
                        Bal: <strong>${{ number_format($project->balance, 2) }}</strong>
                    </div>
                    <form>
                        <div class="form-group mb-2">
                            <label class="small mb-1">Currency</label>
                            <select class="form-control form-control-sm" wire:model="currency">
                                <option value="USD">USD ($)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small mb-1">Amount</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" wire:model="amount">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small mb-1">Method</label>
                            <select class="form-control form-control-sm" wire:model="payment_method">
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="PayPal">PayPal</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small mb-1">Status</label>
                            <select class="form-control form-control-sm" wire:model="payment_status">
                                <option value="Paid">Paid</option>
                                <option value="Partial">Partial</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-block" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        {{ $isEditMode ? 'Update' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
