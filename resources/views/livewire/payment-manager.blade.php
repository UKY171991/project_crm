<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payments</h3>
            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
            <div class="card-tools">
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Record Payment
                </button>
            </div>
            @endif
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 5%">Sr. No.</th>
                        <th>Date</th>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Transaction ID</th>
                        @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                        <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y') }}</td>
                            <td><a href="{{ route('projects.show', $payment->project) }}">{{ $payment->project->title }}</a></td>
                            <td>{{ $payment->project->client->company_name ?? 'N/A' }}</td>
                            <td>
                                <strong>{{ $payment->currency }}</strong> {{ number_format($payment->amount, 2) }}
                            </td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>
                                <span class="badge {{ $payment->payment_status == 'Paid' ? 'badge-success' : ($payment->payment_status == 'Partial' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $payment->payment_status }}
                                </span>
                            </td>
                            <td class="small">{{ $payment->transaction_id ?? '-' }}</td>
                            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                            <td>
                                <button wire:click="edit({{ $payment->id }})" class="btn btn-info btn-xs">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button wire:click="delete({{ $payment->id }})" class="btn btn-danger btn-xs" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Payment' : 'Record New Payment' }}</h5>
                    <button type="button" class="close" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group mb-3">
                            <label>Project</label>
                            <select class="form-control" wire:model="project_id">
                                <option value="">-- Select Project --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->client->company_name ?? 'No Client' }})</option>
                                @endforeach
                            </select>
                            @error('project_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($project_id)
                        <div class="alert alert-info py-2 small mb-3">
                            @php $proj = $projects->find($project_id); @endphp
                            <strong>Project Budget:</strong> {{ $proj->currency ?? 'USD' }} {{ number_format($proj->budget, 2) }} <br>
                            <strong>Remaining Balance:</strong> {{ $proj->currency ?? 'USD' }} {{ number_format($proj->balance, 2) }}
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control" wire:model="currency">
                                        @foreach($activeCurrencies as $cur)
                                            <option value="{{ $cur->code }}">{{ $cur->code }} ({{ $cur->symbol }})</option>
                                        @endforeach
                                    </select>
                                    @error('currency') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="amount">
                                    @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Date</label>
                                    <input type="date" class="form-control" wire:model="payment_date">
                                    @error('payment_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" wire:model="payment_status">
                                        <option value="Paid">Paid</option>
                                        <option value="Partial">Partial</option>
                                        <option value="Unpaid">Unpaid</option>
                                    </select>
                                    @error('payment_status') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Payment Method</label>
                            <select class="form-control" wire:model="payment_method">
                                <option value="">-- Select Method --</option>
                                <option value="UPI">UPI</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="PayPal">PayPal</option>
                            </select>
                            @error('payment_method') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Transaction ID (Optional)</label>
                            <input type="text" class="form-control" wire:model="transaction_id">
                            @error('transaction_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        {{ $isEditMode ? 'Save Changes' : 'Record Payment' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <!-- Datepicker initialized via Alpine.js on the element itself -->
    @endpush
</div>
