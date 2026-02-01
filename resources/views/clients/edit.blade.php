<x-admin-layout>
    <x-slot name="header">Edit Client</x-slot>

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Client: {{ $client->company_name }}</h3>
        </div>
        <form action="{{ route('clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control" required value="{{ old('company_name', $client->company_name) }}">
                    @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Contact Name (User)</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $client->user->name) }}">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Email (Login)</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email', $client->user->email) }}">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control">{{ old('address', $client->address) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Client</button>
            </div>
        </form>
    </div>
</x-admin-layout>
