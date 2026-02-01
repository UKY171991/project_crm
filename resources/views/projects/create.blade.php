<x-admin-layout>
    <x-slot name="header">Create New Project</x-slot>

    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Project Details</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Project Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" value="{{ old('title') }}" required>
                             @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter ...">{{ old('description') }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}">
                        </div>

                        <!-- Client Selection (Admin/Master Only) -->
                        @if(!$clients->isEmpty())
                        <div class="form-group">
                            <label>Select Client</label>
                            <select class="form-control" name="client_id">
                                <option value="">-- Select Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->company_name }} ({{ $client->user->name }})</option>
                                @endforeach
                            </select>
                             @error('client_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Project</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
</x-admin-layout>
