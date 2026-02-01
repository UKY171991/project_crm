<x-admin-layout>
    <x-slot name="header">{{ $project->title }}</x-slot>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Project Detail</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Status</span>
                                    <span class="info-box-number text-center text-muted mb-0">{{ $project->status }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Start Date</span>
                                    <span class="info-box-number text-center text-muted mb-0">{{ $project->start_date }}</span>
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
                        @foreach($project->mediaFiles->where('file_type', 'image') as $media)
                            <div class="col-sm-3">
                                <a href="{{ Storage::url($media->file_path) }}" data-toggle="lightbox" data-title="sample 1 - white">
                                    <img src="{{ Storage::url($media->file_path) }}" class="img-fluid mb-2" alt="white sample"/>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    
                    <h5>Videos</h5>
                    <div class="row">
                        @foreach($project->mediaFiles->where('file_type', 'video') as $media)
                        <div class="col-sm-6">
                            <div class="embed-responsive embed-responsive-16by9">
                                <video controls class="embed-responsive-item">
                                    <source src="{{ Storage::url($media->file_path) }}" type="{{ $media->mime_type }}">
                                </video>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
             <!-- Client Info -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Client Info</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-building mr-1"></i> Company</strong>
                    <p class="text-muted">{{ $project->client->company_name }}</p>
                    <hr>
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                    <p class="text-muted">{{ $project->client->address }}</p>
                </div>
            </div>
            
            <!-- Uploads -->
            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin') || auth()->id() == $project->client->user_id)
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Upload Media</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('projects.upload-image', $project) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Upload Screenshot</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="file" class="custom-file-input" id="exampleInputFile">
                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                </div>
                                <div class="input-group-append">
                                    <button type="submit" class="input-group-text">Upload</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <form action="{{ route('projects.upload-video', $project) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                         <div class="form-group">
                            <label>Upload Video</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="file" class="custom-file-input" id="videoFile">
                                    <label class="custom-file-label" for="videoFile">Choose file</label>
                                </div>
                                <div class="input-group-append">
                                    <button type="submit" class="input-group-text">Upload</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            <!-- Project Assignment (Admin/Master Only) -->
            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Assign Users</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Assigned users can view this project.</p>
                    <ul class="list-group mb-3">
                        @foreach($project->assignees as $assignee)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $assignee->name }}
                                <form action="{{ route('projects.unassign', [$project, $assignee]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-times"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>

                    <form action="{{ route('projects.assign', $project) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <select name="user_id" class="form-control" required>
                                <option value="">Select User...</option>
                                @foreach(\App\Models\User::whereHas('role', function($q){ $q->where('slug','user'); })->get() as $u)
                                    @if(!$project->assignees->contains($u->id))
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->assignedProjects()->whereIn('status', ['Pending','Running'])->count() }} active)</option>
                                    @endif
                                @endforeach
                            </select>
                            <span class="input-group-append">
                                <button type="submit" class="btn btn-warning">Assign</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>
