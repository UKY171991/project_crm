<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Clients</h3>
            <div class="card-tools">
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Client
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 5%">Sr. No.</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th class="text-center">Pending Tasks</th>
                        <th class="text-center">Completed Tasks</th>
                        <th class="text-center">Pending Payment</th>
                        <th class="text-center">Completed Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $client->phone ?? '-' }}</td>
                            <td>{{ $client->company_name }}</td>
                            <td class="text-center">
                                <span class="badge badge-warning" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">
                                    {{ $client->pending_tasks_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">
                                    {{ $client->completed_tasks_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">
                                    {{ $client->currency }} {{ number_format($client->total_pending_payment, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;">
                                    {{ $client->currency }} {{ number_format($client->total_completed_payment, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($client->phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}" target="_blank" class="btn btn-success btn-sm" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                @endif
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-primary btn-sm" title="View Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button wire:click="edit({{ $client->id }})" class="btn btn-info btn-sm" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button wire:click="delete({{ $client->id }})" class="btn btn-danger btn-sm" title="Delete" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No clients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Logic simulated with if/else or bootstrap modal toggling -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Client' : 'Add Client' }}</h5>
                    <button type="button" class="close" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" class="form-control" wire:model="company_name">
                            @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" class="form-control" wire:model="contact_name">
                             @error('contact_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" wire:model="email">
                             @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Password {{ $isEditMode ? '(Leave blank to keep current)' : '' }}</label>
                            <input type="password" class="form-control" wire:model="password">
                             @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" wire:model="phone">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea class="form-control" wire:model="address"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $isEditMode ? 'update' : 'store' }}">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
