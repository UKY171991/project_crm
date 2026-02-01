<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">System Settings</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form wire:submit.prevent="save">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="system_title">System Title</label>
                            <input type="text" class="form-control" id="system_title" placeholder="Enter System Title" wire:model="system_title">
                            @error('system_title') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo">System Logo</label>
                                    @if($current_logo)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $current_logo) }}" alt="Current Logo" style="max-height: 50px;">
                                        </div>
                                    @endif
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="logo" wire:model="new_logo">
                                            <label class="custom-file-label" for="logo">Choose file</label>
                                        </div>
                                    </div>
                                    <div wire:loading wire:target="new_logo" class="text-info mt-1">Uploading...</div>
                                    @error('new_logo') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="favicon">System Favicon</label>
                                    @if($current_favicon)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $current_favicon) }}" alt="Current Favicon" style="max-height: 32px;">
                                        </div>
                                    @endif
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="favicon" wire:model="new_favicon">
                                            <label class="custom-file-label" for="favicon">Choose file</label>
                                        </div>
                                    </div>
                                    <div wire:loading wire:target="new_favicon" class="text-info mt-1">Uploading...</div>
                                    @error('new_favicon') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">Save Changes</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Preview</h3>
                </div>
                <div class="card-body text-center">
                    <h5>Current Logo</h5>
                    <div class="p-4 bg-light rounded mb-3">
                        @if($new_logo)
                            <img src="{{ $new_logo->temporaryUrl() }}" style="max-width: 100%; max-height: 100px;">
                        @elseif($current_logo)
                            <img src="{{ asset('storage/' . $current_logo) }}" style="max-width: 100%; max-height: 100px;">
                        @else
                            <div class="text-muted">No Logo Set</div>
                        @endif
                    </div>

                    <h5>Current Favicon</h5>
                    <div class="p-4 bg-light rounded">
                        @if($new_favicon)
                            <img src="{{ $new_favicon->temporaryUrl() }}" style="max-width: 32px; max-height: 32px;">
                        @elseif($current_favicon)
                            <img src="{{ asset('storage/' . $current_favicon) }}" style="max-width: 32px; max-height: 32px;">
                        @else
                            <div class="text-muted">No Favicon Set</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
