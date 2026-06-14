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
            <div class="card-tools d-flex align-items-center">
                @php
                    $pendingClientsCount = $clients->filter(function($c) {
                        return $c->pending_tasks_count > 0 || $c->total_pending_payment > 0;
                    })->count();
                @endphp
                @if($pendingClientsCount > 0)
                    <span class="badge badge-warning p-2 mr-3">
                        <i class="fas fa-exclamation-triangle"></i> {{ $pendingClientsCount }} Pending Updates
                    </span>
                @endif
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Client
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped text-nowrap">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Client Details</th>
                        <th class="text-center">Last Contact</th>
                        <th class="text-center">Pending Tasks</th>
                        <th class="text-center">Completed Tasks</th>
                        <th class="text-center">Payments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $client->company_name }}</div>
                                <div class="small text-muted"><i class="fas fa-phone-alt fa-xs"></i> {{ $client->phone ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                                @if($client->last_whatsapp_at)
                                    <span class="text-success small font-weight-bold">
                                        {{ $client->last_whatsapp_at->format('M d, h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted small">Never</span>
                                @endif
                            </td>
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
                                <div class="mb-1" title="Pending Payment">
                                    <span class="badge badge-danger" style="font-size: 0.85rem; padding: 0.3rem 0.6rem; min-width: 100px; display: inline-block;">
                                        <small class="d-block text-xs uppercase" style="opacity: 0.8;">Pending</small>
                                        {{ $client->currency }} {{ number_format($client->total_pending_payment, 2) }}
                                    </span>
                                </div>
                                <div title="Completed Payment">
                                    <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.3rem 0.6rem; min-width: 100px; display: inline-block;">
                                        <small class="d-block text-xs uppercase" style="opacity: 0.8;">Paid</small>
                                        {{ $client->currency }} {{ number_format($client->total_completed_payment, 2) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($client->phone)
                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $client->phone);
                                    if(strlen($phone) == 10) $phone = '91' . $phone;
                                    
                                    // Status Summary Message
                                    $statusMessage = $client->getStatusSummaryMessage();
                                    $statusWaUrl = "https://wa.me/" . $phone . "?text=" . urlencode($statusMessage);

                                    // General Offer Message
                                    $hour = date('H');
                                    $greeting = "Good Morning";
                                    if ($hour >= 12 && $hour < 17) {
                                        $greeting = "Good Afternoon";
                                    } elseif ($hour >= 17) {
                                        $greeting = "Good Evening";
                                    }
                                    
                                    $clientName = $client->company_name ?? ($client->user->name ?? 'Client');
                                    $offerMessage = $greeting . " " . $clientName . ",\n\nI am a Website Developer, PHP Developer, Laravel Developer, Codeigniter Developer, and Core PHP Developer. If you have any projects available, please let me know. I'd love to assist you!\n\nRegards,";
                                    $offerWaUrl = "https://wa.me/" . $phone . "?text=" . urlencode($offerMessage);
                                @endphp
                                <div class="btn-group">
                                    <a href="{{ $statusWaUrl }}" target="_blank" wire:click="logWhatsApp({{ $client->id }})" class="btn btn-success btn-sm" title="WhatsApp Status (Pending Projects/Payment)">
                                        <i class="fab fa-whatsapp"></i> Status
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ $statusWaUrl }}" target="_blank" wire:click="logWhatsApp({{ $client->id }})">
                                            <i class="fas fa-info-circle text-success"></i> Send Status Update
                                        </a>
                                        <a class="dropdown-item" href="{{ $offerWaUrl }}" target="_blank" wire:click="logWhatsApp({{ $client->id }})">
                                            <i class="fas fa-bullhorn text-primary"></i> Send Work Offer
                                        </a>
                                    </div>
                                </div>
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
