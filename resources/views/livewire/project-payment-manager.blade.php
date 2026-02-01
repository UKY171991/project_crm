<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title small font-weight-bold"><i class="fas fa-money-check-alt mr-1"></i> PAYMENTS</h3>
        @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
        <div class="card-tools">
            <button wire:click="create" class="btn btn-tool text-success p-0">
                <i class="fas fa-plus-circle fa-lg"></i>
            </button>
        </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if(session()->has('payment_success'))
            <div class="alert alert-success mx-2 mt-2 py-1 small">
                {{ session('payment_success') }}
            </div>
        @endif

        <table class="table table-sm table-valign-middle mb-0">
            <thead>
                <tr class="small text-muted">
                    <th class="pl-3">Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                    <th class="text-right pr-3">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr class="small">
                    <td class="pl-3 text-muted">{{ $payment->payment_date ? $payment->payment_date->format('d/m/y') : $payment->created_at->format('d/m/y') }}</td>
                    <td><span class="font-weight-bold">{{ $payment->currency }}</span> {{ number_format($payment->amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $payment->payment_status == 'Paid' ? 'badge-success' : ($payment->payment_status == 'Partial' ? 'badge-warning' : 'badge-danger') }}" style="font-size: 0.7rem;">
                            {{ $payment->payment_status }}
                        </span>
                    </td>
                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                    <td class="text-right pr-3">
                        <button wire:click="edit({{ $payment->id }})" class="btn btn-xs btn-link text-info p-0 mr-1">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button wire:click="delete({{ $payment->id }})" class="btn btn-xs btn-link text-danger p-0" onclick="confirm('Delete payment?') || event.stopImmediatePropagation()">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3 small italic">No payments recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title font-weight-bold">{{ $isEditMode ? 'Edit' : 'Record' }} Payment</h6>
                    <button type="button" class="close" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3">
                    <form>
                        <div class="form-group mb-2">
                            <label class="small text-muted mb-0">Currency</label>
                            <select class="form-control form-control-sm" wire:model="currency">
                                <option value="USD">USD ($)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small text-muted mb-0">Amount</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" wire:model="amount">
                            @error('amount') <span class="text-danger xsmall">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="small text-muted mb-0">Payment Date</label>
                            <div wire:ignore>
                                <input type="text" class="form-control form-control-sm datepicker" wire:model="payment_date" autocomplete="off">
                            </div>
                            @error('payment_date') <span class="text-danger xsmall">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="small text-muted mb-0">Method</label>
                            <select class="form-control form-control-sm" wire:model="payment_method">
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="PayPal">PayPal</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small text-muted mb-0">Status</label>
                            <select class="form-control form-control-sm" wire:model="payment_status">
                                <option value="Paid">Paid</option>
                                <option value="Partial">Partial</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-primary btn-sm btn-block shadow-sm" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        {{ $isEditMode ? 'Update Payment' : 'Save Payment' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            initPaymentDatePickers();
        });

        Livewire.hook('message.processed', (message, component) => {
            initPaymentDatePickers();
        });

        function initPaymentDatePickers() {
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                allowInput: true,
                static: true
            });
        }
    </script>
    @endpush
</div>
