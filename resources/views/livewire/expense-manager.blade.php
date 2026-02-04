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
            <h3 class="card-title">Expenses</h3>
            <div class="card-tools">
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Record Expense
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 5%">Sr. No.</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Project</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : $expense->created_at->format('d M Y') }}</td>
                            <td>{{ Str::limit($expense->description, 50) }}</td>
                            <td>{{ $expense->category ?? '-' }}</td>
                            <td>
                                @if($expense->project)
                                    <a href="{{ route('projects.show', $expense->project) }}">{{ $expense->project->title }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $expense->currency }}</strong> {{ number_format($expense->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $expense->status == 'Paid' ? 'badge-success' : ($expense->status == 'Pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $expense->status }}
                                </span>
                            </td>
                            <td>
                                <button wire:click="edit({{ $expense->id }})" class="btn btn-info btn-xs">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button wire:click="delete({{ $expense->id }})" class="btn btn-danger btn-xs" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No expenses found.</td>
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
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Expense' : 'Record New Expense' }}</h5>
                    <button type="button" class="close" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                                               <div class="form-group mb-3">
                            <label>Description</label>
                            <input type="text" class="form-control" wire:model="description" placeholder="e.g. Flight tickets, Server cost, Lunch">
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category (Optional)</label>
                                    <input type="text" class="form-control" wire:model="category" list="expense_categories">
                                    <datalist id="expense_categories">
                                        <option value="Travel">
                                        <option value="Food">
                                        <option value="Software">
                                        <option value="Hardware">
                                        <option value="Office Supplies">
                                        <option value="Home Accessory">
                                    </datalist>
                                    @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Project (Optional)</label>
                                    <select class="form-control" wire:model="project_id">
                                        <option value="">-- No Project --</option>
                                        @foreach($projects as $p)
                                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('project_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

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
                                    <label>Expense Date</label>
                                    <input type="date" class="form-control" wire:model="expense_date">
                                    @error('expense_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" wire:model="status">
                                        <option value="Paid">Paid</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                    @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        {{ $isEditMode ? 'Save Changes' : 'Record Expense' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <!-- Datepicker initialized via Alpine.js x-init on the element itself -->
    @endpush
</div>
