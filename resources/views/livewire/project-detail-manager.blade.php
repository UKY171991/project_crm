<div class="row">
    <div class="col-md-12">
        <div class="row">
        @if(session()->has('success'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        </div>

        <div class="row">
        <!-- DETAIL COLUMN -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Project Detail</h3>
                </div>
                <div class="card-body">
                    <div class="row text-uppercase">
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Budget</span>
                                    <span class="info-box-number text-center text-primary mb-0">{{ $project->currency }} {{ number_format($project->budget, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Total Paid</span>
                                    <span class="info-box-number text-center text-success mb-0">{{ $project->currency }} {{ number_format($project->total_paid, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Balance</span>
                                    <span class="info-box-number text-center {{ $project->balance > 0 ? 'text-danger' : 'text-primary' }} mb-0">{{ $project->currency }} {{ number_format($project->balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content text-center">
                                    <span class="info-box-text text-muted">Status</span>
                                    <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : 'badge-secondary') }}">
                                        {{ $project->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content text-center">
                                    <span class="info-box-text text-muted">Start Date</span>
                                    <span class="info-box-number text-muted mb-0 small">
                                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : 'Not Set' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content text-center">
                                    <span class="info-box-text text-muted">Due Date</span>
                                    <span class="info-box-number {{ \Carbon\Carbon::parse($project->end_date)->isPast() && $project->status != 'Completed' && $project->end_date ? 'text-danger font-weight-bold' : 'text-muted' }} mb-0 small">
                                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : 'Not Set' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-muted border-bottom pb-2">Description</h5>
                            <div class="p-2">
                                <p class="text-muted">{{ $project->description ?: 'No description provided.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
             <!-- Media Gallery -->
             <div class="card card-secondary">
                <div class="card-header border-0">
                    <h3 class="card-title">Gallery</h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-camera mr-1"></i> Screenshots</h6>
                    <hr class="mt-1 mb-3">
                    <div class="row mb-4">
                        @forelse($project->mediaFiles->where('file_type', 'image') as $media)
                            <div class="col-sm-3 mb-3 position-relative">
                                <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $media->file_path) }}" class="img-fluid rounded shadow-sm border" alt="Screenshot"/>
                                </a>
                                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || auth()->id() == $media->uploaded_by)
                                <button wire:click="deleteMedia({{ $media->id }})" 
                                        class="btn btn-xs btn-danger position-absolute" 
                                        style="top:0; right:15px; border-radius: 50%; width: 20px; height: 20px; padding: 0;"
                                        onclick="confirm('Delete this image?') || event.stopImmediatePropagation()">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        @empty
                            <div class="col-12 text-muted small px-3">No screenshots uploaded.</div>
                        @endforelse
                    </div>
                    
                    <h6><i class="fas fa-video mr-1"></i> Videos</h6>
                    <hr class="mt-1 mb-3">
                    <div class="row">
                        @forelse($project->mediaFiles->where('file_type', 'video') as $media)
                        <div class="col-sm-6 mb-3">
                            <div class="card bg-dark shadow-sm">
                                <div class="card-body p-0 position-relative">
                                    @php
                                        $extension = strtolower(pathinfo($media->file_path, PATHINFO_EXTENSION));
                                        $isPlayable = in_array($extension, ['mp4', 'webm', 'ogg']);
                                    @endphp

                                    @if($isPlayable)
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <video controls class="embed-responsive-item">
                                                <source src="{{ asset('storage/' . $media->file_path) }}" type="{{ $media->mime_type }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    @else
                                        <div class="p-5 text-center bg-secondary" style="height: 180px;">
                                            <i class="fas fa-video-slash fa-2x mb-2"></i>
                                            <p class="small mb-2">Format ({{ $extension }}) not supported for browser playback</p>
                                            <a href="{{ asset('storage/' . $media->file_path) }}" class="btn btn-xs btn-light" download>
                                                <i class="fas fa-download"></i> Download to View
                                            </a>
                                        </div>
                                    @endif

                                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || auth()->id() == $media->uploaded_by)
                                    <button wire:click="deleteMedia({{ $media->id }})" 
                                            class="btn btn-xs btn-danger position-absolute" 
                                            style="top:5px; right:5px; z-index:10; border-radius: 50%; width: 20px; height: 20px; padding: 0;"
                                            onclick="confirm('Delete this video?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </div>
                                <div class="card-footer py-1 px-2 small bg-secondary d-flex justify-content-between align-items-center">
                                    <span class="text-truncate" style="max-width: 80%;">{{ $media->file_name }}</span>
                                    <a href="{{ asset('storage/' . $media->file_path) }}" class="text-white" download><i class="fas fa-download"></i></a>
                                </div>
                            </div>
                        </div>
                        @empty
                             <div class="col-12 text-muted small px-3">No videos uploaded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- SIDEBAR COLUMN -->
        <div class="col-md-4">
             <!-- Client Info -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Client Info</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-building mr-1"></i> Company</strong>
                    <p class="text-muted">{{ $project->client->company_name ?? 'N/A' }}</p>
                    <hr>
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                    <p class="text-muted">{{ $project->client->address ?? 'N/A' }}</p>
                </div>
            </div>
            
            <!-- Uploads -->
            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || (auth()->user()->hasRole('client') && auth()->user()->clientProfile->id == $project->client_id))
            <div class="card card-success">
                <div class="card-header border-0">
                    <h3 class="card-title">Upload Media</h3>
                </div>
                <div class="card-body">
                    <div class="form-group pb-2 mb-2 border-bottom">
                        <label class="small text-muted mb-1">Upload Screenshot (Max 10MB)</label>
                        <div class="custom-file custom-file-sm">
                             <input type="file" class="custom-file-input" id="photoUpload" wire:model="photo">
                             <label class="custom-file-label text-truncate" for="photoUpload">
                                 @if($photo) {{ $photo->getClientOriginalName() }} @else Choose Image @endif
                             </label>
                        </div>
                        <div wire:loading wire:target="photo" class="text-info text-xs mt-1"><i class="fas fa-spinner fa-spin mr-1"></i>Uploading...</div>
                        @error('photo') <span class="text-danger xsmall">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="small text-muted mb-1">Upload Video (Max 200MB - MP4, WebM, MKV, etc.)</label>
                        <div class="custom-file custom-file-sm">
                             <input type="file" class="custom-file-input" id="videoUpload" wire:model="video">
                             <label class="custom-file-label text-truncate" for="videoUpload">
                                  @if($video) {{ $video->getClientOriginalName() }} @else Choose Video @endif
                             </label>
                        </div>
                        <div wire:loading wire:target="video" class="text-info text-xs mt-1"><i class="fas fa-spinner fa-spin mr-1"></i>Uploading...</div>
                         @error('video') <span class="text-danger xsmall">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            @endif

            <!-- Payments Section -->
            @livewire('project-payment-manager', ['project' => $project])

            <!-- Assign Users -->
            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
            <div class="card card-warning">
                <div class="card-header border-0">
                    <h3 class="card-title text-white">Assign Users</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                         <label class="small text-muted">Select User to Assign</label>
                         <div class="input-group input-group-sm">
                            <select class="form-control" wire:model="user_to_assign">
                                <option value="">Select User...</option>
                                @foreach($assignableUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-append">
                                <button type="button" class="btn btn-warning btn-flat text-white" wire:click="assignUser">Assign</button>
                            </span>
                         </div>
                         @error('user_to_assign') <span class="text-danger xsmall">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-3">
                        <h6>Assigned Users</h6>
                        <ul class="list-unstyled">
                            @forelse($project->assignees as $assignee)
                                <li class="mb-2 d-flex justify-content-between align-items-center p-2 bg-light rounded border">
                                    <span><i class="fas fa-user-circle mr-1 text-muted"></i> {{ $assignee->name }}</span>
                                    <button wire:click="removeUser({{ $assignee->id }})" 
                                            class="btn btn-xs btn-outline-danger" 
                                            onclick="confirm('Remove this user?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-user-minus"></i>
                                    </button>
                                </li>
                            @empty
                                <li class="text-muted small">No users assigned.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            @else
            <div class="card card-warning card-outline">
                 <div class="card-header">
                    <h3 class="card-title">Assigned Team</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($project->assignees as $assignee)
                            <li class="list-group-item small"><i class="fas fa-user mr-2 text-muted"></i> {{ $assignee->name }}</li>
                        @empty
                            <li class="list-group-item small text-muted">No users assigned.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            @endif
        </div>
        </div>
    </div>
</div>
