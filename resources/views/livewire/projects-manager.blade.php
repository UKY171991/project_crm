<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Projects</h3>
            <div class="card-tools">
                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('client'))
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create Project
                </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 5%">Sr. No.</th>
                        <th style="width: 20%">Project Title</th>
                        <th style="width: 15%">Client</th>
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
                    @foreach($projects as $project)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="font-weight-bold">{{ $project->title }}</a>
                                <br/>
                                <small>Created {{ $project->created_at->format('d.m.Y') }}</small>
                            </td>
                            <td>{{ $project->client->company_name ?? 'N/A' }}</td>
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
                                <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : 'badge-secondary') }}">
                                    {{ $project->status }}
                                </span>
                            </td>
                            <td class="project-actions text-right">
                                <a class="btn btn-primary btn-sm" href="{{ route('projects.show', $project) }}">
                                    <i class="fas fa-folder"></i> View
                                </a>
                                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || (auth()->user()->hasRole('client') && $project->client_id == auth()->user()->clientProfile->id))
                                    <button wire:click="edit({{ $project->id }})" class="btn btn-info btn-sm">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </button>
                                @endif
                                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                                    <button wire:click="delete({{ $project->id }})" class="btn btn-danger btn-sm" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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

                        <div class="row">
                            @if($isEditMode)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" wire:model="status">
                                        <option value="Pending">Pending</option>
                                        <option value="Running">Running</option>
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
