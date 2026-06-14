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
            <h3 class="card-title">Loan Management</h3>
            <div class="card-tools">
                <div class="d-flex align-items-center gap-2">
                    <button wire:click="create('loan')" class="btn btn-primary btn-sm ml-2">
                        <i class="fas fa-plus"></i> Add Loan
                    </button>
                    <button wire:click="create('emi')" class="btn btn-success btn-sm ml-2">
                        <i class="fas fa-hand-holding-usd"></i> Add EMI
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Summary Section -->
            <div class="row p-3 bg-light text-center">
                <div class="col-md-4">
                    <div class="card bg-primary text-white mb-0 shadow-sm">
                        <div class="card-body py-2">
                            <h6 class="mb-0">Total Loan</h6>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($totalLoan, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white mb-0 shadow-sm">
                        <div class="card-body py-2">
                            <h6 class="mb-0">EMI Balance (Paid)</h6>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($totalEMI, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white mb-0 shadow-sm">
                        <div class="card-body py-2">
                            <h6 class="mb-0">Remaining Balance</h6>
                            <h4 class="mb-0 font-weight-bold">{{ number_format($totalBalance, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs Section -->
            <ul class="nav nav-tabs px-3 mt-2" id="loanTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'all' ? 'active' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'all')">All Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'loans' ? 'active' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'loans')">Loans</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'emis' ? 'active' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'emis')">Paid EMIs</a>
                </li>
            </ul>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="width: 5%">Sr. No.</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reference Loan</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $loan->loan_date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge {{ $loan->amount > 0 ? 'badge-primary' : 'badge-success' }}">
                                        {{ $loan->amount > 0 ? 'Loan' : 'EMI' }}
                                    </span>
                                    <br><small class="text-muted">{{ $loan->loan_type }}</small>
                                </td>
                                <td>
                                    @if($loan->parent)
                                        <span class="text-info">#{{ $loan->parent->id }} - {{ $loan->parent->loan_type }}</span>
                                    @elseif($loan->amount > 0)
                                        <span class="text-muted">Main Loan #{{ $loan->id }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $loan->description ?? '-' }}</td>
                                <td class="{{ $loan->amount > 0 ? 'text-danger' : 'text-success' }}">
                                    <strong>{{ $loan->amount > 0 ? '+' : '' }}{{ number_format($loan->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <button wire:click="edit({{ $loan->id }})" class="btn btn-info btn-xs">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button wire:click="delete({{ $loan->id }})" class="btn btn-danger btn-xs" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No loan transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($isEditMode) 
                            Edit Transaction
                        @else
                            {{ $transactionType == 'emi' ? 'Add EMI Payment' : 'Record New Loan' }}
                        @endif
                    </h5>
                    <button type="button" class="close" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert {{ $transactionType == 'emi' ? 'alert-success' : 'alert-info' }} py-2 text-center">
                        <strong>{{ $transactionType == 'emi' ? 'RECORDING EMI PAYMENT' : 'RECORDING LOAN DISBURSEMENT' }}</strong>
                    </div>
                    <form>

                        <div class="form-group mb-3">
                            <label>Loan Type (Category)</label>
                            <input type="text" class="form-control" wire:model="loan_type" placeholder="e.g. Salary Advance, Personal Loan">
                            @error('loan_type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($transactionType == 'emi')
                        <div class="form-group mb-3">
                            <label>Select Loan (To Pay Against)</label>
                            <select class="form-control" wire:model="parent_id">
                                <option value="">-- Select Active Loan --</option>
                                @foreach($parentLoanList as $pl)
                                    <option value="{{ $pl->id }}">#{{ $pl->id }} - {{ $pl->loan_type }} (Bal: {{ number_format($pl->amount - $pl->emis()->sum('amount'), 2) }})</option>
                                @endforeach
                            </select>
                            @error('parent_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="form-group mb-3">
                            <label>Amount</label>
                            <input type="number" step="0.01" class="form-control" wire:model="amount" placeholder="e.g. 500 or -500">
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Date</label>
                            <input type="date" class="form-control" wire:model="loan_date">
                            @error('loan_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Description</label>
                            <input type="text" class="form-control" wire:model="description" placeholder="Optional description">
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if(!$isEditMode && $amount > 0)
                        <div class="form-group mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="recordAsExpense" wire:model="recordAsExpense">
                                <label class="custom-control-label" for="recordAsExpense">Record this as an Expense</label>
                            </div>
                            <small class="text-muted">If checked, an entry will be added to the Expenses table automatically.</small>
                        </div>
                        @endif

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        {{ $isEditMode ? 'Save Transaction' : 'Record Transaction' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
