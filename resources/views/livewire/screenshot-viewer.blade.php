<div>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-camera text-primary"></i> Screenshots
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Filters -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small text-muted mb-1">User</label>
                            <select wire:model.live="selectedUser" class="form-control form-control-sm">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted mb-1">Date</label>
                            <input type="date" wire:model.live="selectedDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted mb-1">View Mode</label>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <button type="button" wire:click="$set('viewMode', 'grid')" class="btn {{ $viewMode == 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-th"></i> Grid
                                </button>
                                <button type="button" wire:click="$set('viewMode', 'list')" class="btn {{ $viewMode == 'list' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-list"></i> List
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 text-right">
                            <label class="small text-muted mb-1 d-block">Total</label>
                            <span class="badge badge-primary badge-pill px-3 py-2">
                                {{ $screenshots->total() }} Screenshots
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if($screenshots->isEmpty())
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No screenshots found</h5>
                        <p class="text-muted small">Screenshots will appear here once they are captured.</p>
                    </div>
                </div>
            @else
                @if($viewMode == 'grid')
                    <!-- Grid View -->
                    <div class="row">
                        @foreach($screenshots as $screenshot)
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="position-relative">
                                        <a href="{{ asset('storage/' . $screenshot->path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $screenshot->path) }}" 
                                                 class="card-img-top" 
                                                 style="height: 200px; object-fit: cover; cursor: pointer;"
                                                 alt="Screenshot">
                                        </a>
                                        <button wire:click="deleteScreenshot({{ $screenshot->id }})" 
                                                onclick="return confirm('Delete this screenshot?')"
                                                class="btn btn-sm btn-danger position-absolute" 
                                                style="top: 5px; right: 5px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 24px; height: 24px; font-size: 0.7rem; color: #fff;">
                                                {{ substr($screenshot->user->name, 0, 1) }}
                                            </div>
                                            <small class="font-weight-bold">{{ $screenshot->user->name }}</small>
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="far fa-clock"></i> {{ $screenshot->captured_at->format('M d, Y h:i A') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- List View -->
                    <div class="card shadow-sm border-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 100px;">Preview</th>
                                        <th>User</th>
                                        <th>Captured At</th>
                                        <th>Attendance</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($screenshots as $screenshot)
                                        <tr>
                                            <td>
                                                <a href="{{ asset('storage/' . $screenshot->path) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $screenshot->path) }}" 
                                                         class="img-thumbnail" 
                                                         style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;">
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 30px; height: 30px; font-size: 0.8rem; color: #fff;">
                                                        {{ substr($screenshot->user->name, 0, 1) }}
                                                    </div>
                                                    {{ $screenshot->user->name }}
                                                </div>
                                            </td>
                                            <td>{{ $screenshot->captured_at->format('M d, Y h:i A') }}</td>
                                            <td>
                                                @if($screenshot->attendance)
                                                    <small class="text-muted">
                                                        {{ $screenshot->attendance->date->format('M d, Y') }}
                                                        <br>
                                                        {{ $screenshot->attendance->clock_in->format('h:i A') }} - 
                                                        {{ $screenshot->attendance->clock_out ? $screenshot->attendance->clock_out->format('h:i A') : 'Active' }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ asset('storage/' . $screenshot->path) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt"></i> View
                                                </a>
                                                <button wire:click="deleteScreenshot({{ $screenshot->id }})" 
                                                        onclick="return confirm('Delete this screenshot?')"
                                                        class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $screenshots->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
