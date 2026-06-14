<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0 font-weight-bold text-dark">
                        <i class="fas fa-user-tag text-info mr-2"></i>Leads (Non-Clients)
                    </h4>
                </div>
                <div class="col-auto pr-1">
                    <div class="input-group input-group-sm rounded-pill shadow-sm bg-white overflow-hidden" style="border: 1px solid #ced4da; width: 250px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-0 text-muted px-3"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" wire:model.live="searchTerm" class="form-control border-0 shadow-none px-0 bg-white" placeholder="Search leads...">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 small text-muted">Attached Plan:</span>
                        <a href="{{ asset('assets/images/dev-plan.png') }}" target="_blank">
                            <img src="{{ asset('assets/images/dev-plan.png') }}" alt="Dev Plan" class="rounded shadow-sm border" style="height: 35px; width: 50px; object-fit: cover;">
                        </a>
                    </div>
                </div>
                <div class="col-auto">
                    <button wire:click="create" class="btn btn-primary btn-sm rounded-pill shadow-sm px-3">
                        <i class="fas fa-plus mr-1"></i> Add New Lead
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase font-weight-bold">
                        <th class="px-4 py-3 border-0" style="width: 5%">#</th>
                        <th class="py-3 border-0">Company</th>
                        <th class="py-3 border-0">Contact Name</th>
                        <th class="py-3 border-0">Phone</th>
                        <th class="py-3 border-0">Address</th>
                        <th class="py-3 border-0">Last Message</th>

                        <th class="py-3 border-0">Status</th>
                        <th class="py-3 border-0">Joined Date</th>
                        <th class="py-3 border-0">Next Schedule</th>
                        <th class="px-4 py-3 border-0 text-right">Actions</th>

                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($clients as $client)
                        <tr>
                            <td class="px-4">{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-bold text-dark">{{ $client->company_name }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm mr-2 bg-soft-info text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-weight: bold; background: rgba(23, 162, 184, 0.1);">
                                        {{ strtoupper(substr($client->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="small font-weight-bold">{{ $client->user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($client->phone)
                                    <span class="text-dark"><i class="fas fa-phone-alt small text-muted mr-1"></i>{{ $client->phone }}</span>
                                @else
                                    <span class="text-muted font-italic small">No phone</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small d-inline-block text-truncate" style="max-width: 150px;" title="{{ $client->address }}">
                                    {{ $client->address ?: 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($client->last_whatsapp_at)

                                    <span class="text-success small font-weight-bold">
                                        <i class="fab fa-whatsapp mr-1"></i>{{ $client->last_whatsapp_at->format('M d, h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted small">Never</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $latestFeedback = collect($client->feedbacks)->first();
                                    $statusColor = match($latestFeedback['status'] ?? '') {
                                        'Connected' => 'success',
                                        'Not Reachable' => 'danger',
                                        'Busy' => 'warning',
                                        'Follow Up' => 'info',
                                        'Converted' => 'primary',
                                        default => 'secondary'
                                    };
                                @endphp
                                @if($latestFeedback)
                                    <span class="badge badge-{{ $statusColor }} px-2 py-1" style="font-size: 0.7rem;">
                                        {{ $latestFeedback['status'] }}
                                    </span>
                                @else
                                    <span class="text-muted small">New</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $client->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                @if($latestFeedback && !empty($latestFeedback['next_schedule']))
                                    @php
                                        $sched = \Carbon\Carbon::parse($latestFeedback['next_schedule']);
                                        $isPast = $sched->isPast();
                                        $isToday = $sched->isToday();
                                    @endphp
                                    <span class="small font-weight-bold {{ $isPast ? 'text-danger' : ($isToday ? 'text-warning' : 'text-primary') }}">
                                        <i class="far fa-clock mr-1"></i>{{ $sched->format('M d, h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                                    <td class="px-4 text-right">
                                        <div class="d-flex justify-content-end align-items-center">
                                            @if($client->phone)
                                            @php
                                                $phone = preg_replace('/[^0-9]/', '', $client->phone);
                                                if(strlen($phone) == 10) $phone = '91' . $phone;
                                                
                                                $clientName = $client->company_name ?? ($client->user->name ?? 'there');
                                                $message = "Hi " . $clientName . "! This is Umakant Yadav.\n\nI help businesses and professionals build stunning, modern websites at the lowest budget possible. Whether you need a simple portfolio, a business site, or an e-commerce store, we deliver premium quality without the heavy price tag.\n\nLet me know if you'd be open to a quick chat to see some of our work or get a free quote!";
                                            @endphp
                                            @if(config('services.whatsapp.enabled'))
                                            <button 
                                                type="button"
                                                wire:click="sendProposal({{ $client->id }})"
                                                wire:loading.attr="disabled"
                                                class="btn btn-success btn-sm rounded-pill px-2 mr-1 shadow-sm" 
                                                title="Send WhatsApp Proposal (API)">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                            @else
                                            <button 
                                                type="button"
                                                class="btn btn-success btn-sm rounded-pill px-2 mr-1 shadow-sm" 
                                                onclick="handleWaProposal(this, {{ $client->id }}, '{{ $phone }}', `{{ $message }}`)"
                                                title="WhatsApp (Manual)">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                            @endif
                                            @endif
                                            
                                            <button 
                                                wire:click="openFeedbackModal({{ $client->id }})"
                                                class="btn btn-warning btn-sm rounded-pill px-2 mr-2 shadow-sm text-white" 
                                                title="Call Feedback">
                                                <i class="fas fa-phone-alt"></i>
                                            </button>

                                            
                                            <div class="btn-group shadow-sm rounded overflow-hidden">
                                                <button wire:click="convertToClient({{ $client->id }})" class="btn btn-info btn-sm" title="Convert to Client" onclick="confirm('Convert this lead to a client? This will create a default project for them.') || event.stopImmediatePropagation()">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                                <button wire:click="edit({{ $client->id }})" class="btn btn-light btn-sm text-primary" title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <button wire:click="delete({{ $client->id }})" class="btn btn-light btn-sm text-danger" title="Delete" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-user-friends fa-3x text-light mb-3"></i>
                                <h5 class="text-muted">No leads found</h5>
                                <p class="text-muted small">All clients have active or completed projects.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
        <div class="card-footer bg-white border-top pt-3 pb-0">
            {{ $clients->links() }}
        </div>
        @endif
    </div>

    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-plus mr-2"></i>{{ $isEditMode ? 'Edit Lead' : 'Add New Lead' }}
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    @if(session()->has('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif
                    <form>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase">Company Name</label>
                            <input type="text" class="form-control" wire:model="company_name" placeholder="Enter company name">
                            @error('company_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase">Contact Name</label>
                            <input type="text" class="form-control" wire:model="contact_name" placeholder="Enter contact person name">
                             @error('contact_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase">Phone Number</label>
                            <input type="text" class="form-control" wire:model="phone" placeholder="e.g. +910000000000">
                            @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-muted text-uppercase">Address</label>
                            <textarea class="form-control" wire:model="address" rows="3" placeholder="Enter full address"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        {{ $isEditMode ? 'Update Lead' : 'Save Lead' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showFeedbackModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-phone-volume mr-2"></i>Call Feedbacks
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-5 border-right">
                            <h6 class="font-weight-bold mb-3 text-uppercase small text-muted">Add New Feedback</h6>
                            <form wire:submit.prevent="saveFeedback">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Call Status</label>
                                    <select class="form-control" wire:model="feedbackStatus">
                                        <option value="Connected">Connected</option>
                                        <option value="Not Reachable">Not Reachable</option>
                                        <option value="Busy">Busy</option>
                                        <option value="Follow Up">Follow Up</option>
                                        <option value="Wrong Number">Wrong Number</option>
                                        <option value="Interested">Interested</option>
                                        <option value="Not Interested">Not Interested</option>
                                        <option value="Converted">Converted</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Next Schedule (Optional)</label>
                                    <input type="datetime-local" class="form-control" wire:model="nextSchedule">
                                    @error('nextSchedule') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Feedback Note</label>
                                    <textarea class="form-control" wire:model="feedbackText" rows="4" placeholder="What happened on the call?"></textarea>
                                    @error('feedbackText') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="btn btn-warning btn-block text-white font-weight-bold rounded-pill shadow-sm">
                                    Save Feedback
                                </button>
                            </form>
                        </div>
                        <div class="col-md-7">
                            <h6 class="font-weight-bold mb-3 text-uppercase small text-muted">Call History</h6>
                            <div style="max-height: 400px; overflow-y: auto;">
                                @forelse($clientFeedbacks as $fb)
                                    <div class="mb-3 p-3 rounded bg-light border-left border-warning" style="border-left-width: 4px !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge badge-info small">{{ $fb['status'] }}</span>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($fb['created_at'])->format('M d, h:i A') }}</small>
                                        </div>
                                        @if(!empty($fb['next_schedule']))
                                        <div class="mb-1">
                                            <span class="badge badge-primary" style="font-size: 0.7rem;">
                                                <i class="far fa-clock mr-1"></i>Next: {{ \Carbon\Carbon::parse($fb['next_schedule'])->format('M d, Y h:i A') }}
                                            </span>
                                        </div>
                                        @endif
                                        <p class="mb-1 small text-dark">{{ $fb['feedback'] }}</p>
                                        <div class="text-right">
                                            <small class="text-muted font-italic" style="font-size: 0.7rem;">- By {{ $fb['creator']['name'] ?? 'System' }}</small>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-history fa-2x mb-2 opacity-50"></i>
                                        <p class="small">No previous call history found.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif


    <script>
        function handleWaProposal(btnElem, clientId, phone, message) {
            const btn = $(btnElem);
            const waUrl = "https://wa.me/" + phone + "?text=" + encodeURIComponent(message);
            
            // 1. Visual feedback
            const originalIconClass = btn.find('i').attr('class');
            btn.find('i').attr('class', 'fas fa-spinner fa-spin');
            btn.prop('disabled', true);

            // 2. Copy Image to Clipboard
            const imageUrl = "{{ asset('assets/images/dev-plan.png') }}";
            
            fetch(imageUrl)
                .then(response => response.blob())
                .then(blob => {
                    const item = new ClipboardItem({ "image/png": blob });
                    return navigator.clipboard.write([item]);
                })
                .then(() => {
                    // 3. Open WhatsApp
                    window.open(waUrl, '_blank');
                    
                    // 4. Log to database via Livewire
                    @this.logWhatsApp(clientId);
                    
                    // Reset UI
                    setTimeout(() => {
                        btn.find('i').attr('class', originalIconClass);
                        btn.prop('disabled', false);
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Image copied! Press Ctrl+V in WhatsApp chat.');
                        } else {
                            alert('Image copied! Press Ctrl+V in WhatsApp chat.');
                        }
                    }, 500);
                })
                .catch(err => {
                    console.error("Clipboard Error: ", err);
                    window.open(waUrl, '_blank'); // Fallback: just open link
                    @this.logWhatsApp(clientId);
                    btn.find('i').attr('class', originalIconClass);
                    btn.prop('disabled', false);
                });
        }
    </script>
</div>
