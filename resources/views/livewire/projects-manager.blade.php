<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(!auth()->user()->hasRole('user'))
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md mb-2">
            <div class="card bg-info text-white h-100 mb-0 shadow-sm">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Month Pending</h6>
                    <h4 class="mb-0 font-weight-bold">{{ $currencySymbol }} {{ number_format($stats['current_month']['pending'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md mb-2">
            <div class="card bg-success text-white h-100 mb-0 shadow-sm">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Month Completed</h6>
                    <h4 class="mb-0 font-weight-bold">{{ $currencySymbol }} {{ number_format($stats['current_month']['completed'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md mb-2">
            <div class="card bg-warning text-dark h-100 mb-0 shadow-sm">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Yearly Pending</h6>
                    <h4 class="mb-0 font-weight-bold">{{ $currencySymbol }} {{ number_format($stats['yearly']['pending'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md mb-2">
            <div class="card bg-primary text-white h-100 mb-0 shadow-sm">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Yearly Completed</h6>
                    <h4 class="mb-0 font-weight-bold">{{ $currencySymbol }} {{ number_format($stats['yearly']['completed'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-12 col-md mb-2">
            <div class="card bg-danger text-white h-100 mb-0 shadow-sm">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                    <h6 class="text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">All Time Pending</h6>
                    <h4 class="mb-0 font-weight-bold">{{ $currencySymbol }} {{ number_format($stats['all_time']['pending'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Projects</h3>
            <div class="card-tools">
                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('client'))
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Create Project
                </button>
                @endif
            </div>
        </div>
        <div class="card-body p-3 bg-light border-bottom">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Search projects..." wire:model.live="searchTerm">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" wire:model.live="filterStatus">
                        <option value="">-- All Status --</option>
                        <option value="Pending">Pending</option>
                        <option value="Running">Running</option>
                        <option value="Pending Payment">Pending Payment</option>
                        <option value="Completed">Completed</option>
                        <option value="Canceled">Canceled</option>
                    </select>
                </div>
                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" wire:model.live="filterClient">
                        <option value="">-- All Clients --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2 text-right">
                    <button wire:click="$set('searchTerm', ''); $set('filterStatus', ''); $set('filterClient', '');" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-sync mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 5%">Sr. No.</th>
                        <th style="width: 20%">Project Title</th>
                        @if(!auth()->user()->hasRole('user'))
                        <th>Budget</th>
                        <th>Paid</th>
                        @endif
                        <th>Due Date</th>
                        <th>Status</th>
                        <th style="width: 20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>{{ ($projects->currentPage()-1) * $projects->perPage() + $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="font-weight-bold">{{ $project->title }}</a>
                                <div class="text-muted small">{{ $project->client->company_name ?? 'N/A' }}</div>
                                <small class="text-xs">Created {{ $project->created_at->format('d.m.Y') }}</small>
                            </td>
                            @if(!auth()->user()->hasRole('user'))
                            <td>{{ $project->currency }} {{ number_format($project->budget, 2) }}</td>
                            <td><span class="text-success">{{ $project->currency }} {{ number_format($project->total_paid, 2) }}</span></td>
                            @endif
                            <td>
                                @if($project->end_date)
                                    <span class="{{ \Carbon\Carbon::parse($project->end_date)->isPast() && $project->status != 'Completed' ? 'text-danger font-weight-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : ($project->status == 'Pending Payment' ? 'badge-info' : 'badge-secondary')) }}">
                                    {{ $project->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="btn-group" role="group">
                                    @if($project->client && $project->client->phone)
                                        @php
                                            $phone = preg_replace('/[^0-9]/', '', $project->client->phone);
                                            if(strlen($phone) == 10) $phone = '91' . $phone;
                                            
                                            $clientName = $project->client->user->name ?? $project->client->company_name;
                                            
                                            // Determine greeting based on time
                                            $hour = date('H');
                                            $greeting = "Good Morning";
                                            if ($hour >= 12 && $hour < 17) {
                                                $greeting = "Good Afternoon";
                                            } elseif ($hour >= 17) {
                                                $greeting = "Good Evening";
                                            }
                                            
                                            // Get template based on status
                                            $template = match($project->status) {
                                                'Pending' => config('services.whatsapp.reminder_pending') ?? "{greeting} {name}, your project {title} is currently pending.",
                                                'Running' => config('services.whatsapp.reminder_running') ?? "{greeting} {name}, your project {title} is currently running.",
                                                'Pending Payment' => config('services.whatsapp.reminder_pending_payment') ?? "{greeting} {name}, your project {title} is completed. Please clear the balance of {currency} {balance}.",
                                                'Completed' => config('services.whatsapp.reminder_completed') ?? "{greeting} {name}, your project {title} is completed. Thank you!",
                                                default => "{greeting} {name}, reminder regarding project {title}. Current status: " . $project->status
                                            };
                                            
                                            // Replace placeholders
                                            $message = str_replace(
                                                ['{greeting}', '{name}', '{title}', '{currency}', '{balance}'],
                                                [$greeting, $clientName, $project->title, $project->currency, number_format($project->balance, 2)],
                                                $template
                                            );
                                            
                                            $waUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm" title="Send WhatsApp Reminder">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                    <a class="btn btn-primary btn-sm" href="{{ route('projects.show', $project) }}">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || (auth()->user()->hasRole('client') && $project->client_id == auth()->user()->clientProfile->id))
                                        <button wire:click="edit({{ $project->id }})" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    @endif
                                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                                        <button wire:click="delete({{ $project->id }})" class="btn btn-danger btn-sm" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i><br/>
                                    No projects found matching your criteria.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
        <div class="card-footer clearfix">
            <div class="float-right">
                {{ $projects->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Project' : 'Create New Project' }}</h5>
                    <button type="button" class="close" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>Project Title</label>
                            <input type="text" class="form-control" wire:model="title">
                            @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" wire:model="description" rows="3"></textarea>
                        </div>

                        <!-- Project URLs -->
                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title small font-weight-bold text-muted">Project URLs</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-xs btn-primary shadow-sm" wire:click="addUrl">
                                        <i class="fas fa-plus mr-1"></i> Add URL
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-2">
                                @forelse($project_urls as $index => $item)
                                    <div class="row no-gutters mb-2">
                                        <div class="col-md-4 pr-1">
                                            <input type="text" class="form-control form-control-sm" placeholder="Label (e.g. Admin Panel)" wire:model="project_urls.{{ $index }}.label">
                                        </div>
                                        <div class="col-md-7 pr-1">
                                            <input type="url" class="form-control form-control-sm" placeholder="https://..." wire:model="project_urls.{{ $index }}.url">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-sm btn-danger btn-block" wire:click="removeUrl({{ $index }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted small text-center mb-0 p-2">No URLs added yet. Click 'Add URL' to start.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" wire:model="remarks" rows="2" placeholder="Internal remarks..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control" wire:model="currency">
                                        @foreach($activeCurrencies as $cur)
                                            <option value="{{ $cur->code }}">{{ $cur->code }} ({{ $cur->symbol }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Budget</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="budget">
                                    @error('budget') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" wire:model="start_date">
                                    @error('start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date (End Date)</label>
                                    <input type="date" class="form-control" wire:model="due_date">
                                    @error('due_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Reminder Settings -->
                        <div class="card card-outline card-success mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title small font-weight-bold text-success"><i class="fab fa-whatsapp mr-1"></i> WhatsApp Reminders</h3>
                            </div>
                            <div class="card-body p-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="small mb-1">Reminder Frequency</label>
                                            <select class="form-control form-control-sm" wire:model="reminder_frequency">
                                                <option value="none">None</option>
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="custom-control custom-switch mb-2">
                                            <input type="checkbox" class="custom-control-input" id="enable_reminder" wire:model="reminder_enabled">
                                            <label class="custom-control-label small" for="enable_reminder">Enable Scheduled Reminders</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @if($isEditMode)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" wire:model="status">
                                        <option value="Pending">Pending</option>
                                        <option value="Running">Running</option>
                                        <option value="Pending Payment">Pending Payment</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Canceled">Canceled</option>
                                    </select>
                                </div>
                            </div>
                            @endif

                            @if((auth()->user()->hasRole('master') || auth()->user()->hasRole('admin')))
                            <div class="col-md-{{ $isEditMode ? '6' : '12' }}">
                                <div class="form-group">
                                    <label>Select Client</label>
                                    <select class="form-control" wire:model="client_id">
                                        <option value="">-- Select Client --</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company_name }} ({{ $client->user->name }})</option>
                                        @endforeach
                                    </select>
                                    @error('client_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        {{ $isEditMode ? 'Save Changes' : 'Create Project' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <!-- Datepicker scripts removed from here and moved to Alpine.js x-init for better reliability -->
    @endpush
</div>
