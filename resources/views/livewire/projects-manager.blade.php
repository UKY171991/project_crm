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
                        <th style="width: 20%">Client</th>
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
                            <td>
                                <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : 'badge-secondary') }}">
                                    {{ $project->status }}
                                </span>
                            </td>
                            <td class="project-actions text-right">
                                <a class="btn btn-primary btn-sm" href="{{ route('projects.show', $project) }}">
                                    <i class="fas fa-folder"></i> View
                                </a>
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
                    <h5 class="modal-title">Create New Project</h5>
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
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" class="form-control" wire:model="start_date">
                        </div>

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
                    <button type="button" class="btn btn-primary" wire:click="store">Create Project</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
