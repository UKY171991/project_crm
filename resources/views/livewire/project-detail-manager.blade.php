<div>
    <x-slot name="header">{{ $project->title }}</x-slot>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- DETAILS COLUMN -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Project Detail</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Budget</span>
                                    <span class="info-box-number text-center text-primary mb-0">{{ $project->currency }} {{ number_format($project->budget, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Total Paid</span>
                                    <span class="info-box-number text-center text-success mb-0">{{ $project->currency }} {{ number_format($project->total_paid, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Balance</span>
                                    <span class="info-box-number text-center {{ $project->balance > 0 ? 'text-danger' : 'text-primary' }} mb-0">{{ $project->currency }} {{ number_format($project->balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Status</span>
                                    <span class="info-box-number text-center text-muted mb-0">
                                        <span class="badge {{ $project->status == 'Running' ? 'badge-success' : ($project->status == 'Pending' ? 'badge-warning' : 'badge-secondary') }}">
                                            {{ $project->status }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Start Date</span>
                                    <span class="info-box-number text-center text-muted mb-0">
                                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : 'Not Set' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h4>Description</h4>
                            <div class="post">
                                <p>{{ $project->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
             <!-- Media Gallery -->
             <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Gallery</h3>
                </div>
                <div class="card-body">
                    <h5>Screenshots</h5>
                    <div class="row mb-3">
                        @forelse($project->mediaFiles->where('file_type', 'image') as $media)
                            <div class="col-sm-3 mb-2 position-relative">
                                <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $media->file_path) }}" class="img-fluid rounded" alt="Screenshot"/>
                                </a>
                                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || auth()->id() == $media->uploaded_by)
                                <button wire:click="deleteMedia({{ $media->id }})" 
                                        class="btn btn-xs btn-danger position-absolute" 
                                        style="top:0; right:15px;"
                                        onclick="confirm('Delete this image?') || event.stopImmediatePropagation()">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        @empty
                            <div class="col-12 text-muted">No screenshots uploaded.</div>
                        @endforelse
                    </div>
                    
                    <h5>Videos</h5>
                    <div class="row">
                        @forelse($project->mediaFiles->where('file_type', 'video') as $media)
                        <div class="col-sm-6 mb-2">
                            <div class="card bg-dark">
                                <div class="card-body p-0 position-relative">
                                    @php
                                        $isPlayable = in_array(pathinfo($media->file_path, PATHINFO_EXTENSION), ['mp4', 'webm', 'ogg']);
                                    @endphp

                                    @if($isPlayable)
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <video controls class="embed-responsive-item">
                                                <source src="{{ asset('storage/' . $media->file_path) }}" type="{{ $media->mime_type }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    @else
                                        <div class="p-5 text-center bg-secondary" style="height: 200px;">
                                            <i class="fas fa-video-slash fa-3x mb-2"></i>
                                            <p class="small mb-2">Format ({{ pathinfo($media->file_path, PATHINFO_EXTENSION) }}) not supported for browser playback</p>
                                            <a href="{{ asset('storage/' . $media->file_path) }}" class="btn btn-sm btn-light" download>
                                                <i class="fas fa-download"></i> Download to View
                                            </a>
                                        </div>
                                    @endif

                                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || auth()->id() == $media->uploaded_by)
                                    <button wire:click="deleteMedia({{ $media->id }})" 
                                            class="btn btn-xs btn-danger position-absolute" 
                                            style="top:5px; right:5px; z-index:10;"
                                            onclick="confirm('Delete this video?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </div>
                                <div class="card-footer py-1 px-2 small bg-secondary d-flex justify-content-between">
                                    <span>{{ $media->file_name }}</span>
                                    <a href="{{ asset('storage/' . $media->file_path) }}" class="text-white" download><i class="fas fa-download"></i></a>
                                </div>
                            </div>
                        </div>
                        @empty
                             <div class="col-12 text-muted">No videos uploaded.</div>
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
                <div class="card-header">
                    <h3 class="card-title">Upload Media</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Upload Screenshot (Max 10MB)</label>
                        <div class="custom-file">
                             <input type="file" class="custom-file-input" id="photoUpload" wire:model="photo">
                             <label class="custom-file-label" for="photoUpload">
                                 @if($photo) {{ $photo->getClientOriginalName() }} @else Choose Image @endif
                             </label>
                        </div>
                        <div wire:loading wire:target="photo" class="text-info text-sm mt-1">Uploading...</div>
                        @error('photo') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-group mt-3">
                        <label>Upload Video (Max 200MB - MP4, WebM, MKV, etc.)</label>
                        <div class="custom-file">
                             <input type="file" class="custom-file-input" id="videoUpload" wire:model="video">
                             <label class="custom-file-label" for="videoUpload">
                                  @if($video) {{ $video->getClientOriginalName() }} @else Choose Video @endif
                             </label>
                        </div>
                        <div wire:loading wire:target="video" class="text-info text-sm mt-1">Uploading...</div>
                         @error('video') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            @endif

            <!-- Payments -->
            @livewire('project-payment-manager', ['project' => $project])

            <!-- Project Assignment (Admin/Master Only) -->
            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Assign Users</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted text-sm">Assigned users can view this project.</p>
                    <ul class="list-group mb-3">
                        @foreach($project->assignees as $assignee)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $assignee->name }}
                                <button wire:click="removeUser({{ $assignee->id }})" class="btn btn-xs btn-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="input-group">
                        <select wire:model="user_to_assign" class="form-control form-control-sm">
                            <option value="">Select User...</option>
                            @foreach($assignableUsers as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }} ({{ $u->assignedProjects()->whereIn('status', ['Pending','Running'])->count() }} active)
                                </option>
                            @endforeach
                        </select>
                        <span class="input-group-append">
                            <button wire:click="assignUser" class="btn btn-warning btn-sm" {{ empty($user_to_assign) ? 'disabled' : '' }}>Assign</button>
                        </span>
                    </div>
                     @error('user_to_assign') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
