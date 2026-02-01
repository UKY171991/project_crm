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
                        <th style="width: 20%">Project Title</th>
                        <th style="width: 15%">Client</th>
                        <th>Budget</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th style="width: 20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td>
                                <a>{{ $project->title }}</a>
                                <br/>
                                <small>Created {{ $project->created_at->format('d.m.Y') }}</small>
                            </td>
                            <td>{{ $project->client->company_name ?? 'N/A' }}</td>
                            <td>{{ $project->currency }} {{ number_format($project->budget, 2) }}</td>
                            <td><span class="text-success">{{ $project->currency }} {{ number_format($project->total_paid, 2) }}</span></td>
                            <td>
                                <span class="{{ $project->balance > 0 ? 'text-danger' : 'text-primary' }}">
                                    {{ $project->currency }} {{ number_format($project->balance, 2) }}
                                </span>
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
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
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
                            <textarea class="form-control" wire:model="description"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control" wire:model="currency">
                                        <option value="USD">USD</option>
                                        <option value="INR">INR</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Budget</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="budget">
                                    @error('budget') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" wire:model="start_date">
                                </div>
                            </div>
                        </div>

                        @if($isEditMode)
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" wire:model="status">
                                <option value="Pending">Pending</option>
                                <option value="Running">Running</option>
                                <option value="Completed">Completed</option>
                                <option value="Canceled">Canceled</option>
                            </select>
                        </div>
                        @endif

                        @if((auth()->user()->hasRole('master') || auth()->user()->hasRole('admin')))
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
                        @endif
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
</div>
