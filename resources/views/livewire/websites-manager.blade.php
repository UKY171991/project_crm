<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>{{ \App\Models\Website::count() }}</h3>
                    <p>Total Websites</p>
                </div>
                <div class="icon">
                    <i class="fas fa-globe"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ \App\Models\Website::where('domain_expiry_date', '<', now()->addDays(30))->where('domain_expiry_date', '>=', now())->count() }}</h3>
                    <p>Expiring Soon (30d)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3>{{ \App\Models\Website::where('domain_expiry_date', '<', now())->orWhere('hosting_expiry_date', '<', now())->count() }}</h3>
                    <p>Expired (Dom/Host)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    @php
                        $hostExpiring = \App\Models\Website::where('hosting_expiry_date', '<', now()->addDays(30))->where('hosting_expiry_date', '>=', now())->count();
                    @endphp
                    <h3>{{ $hostExpiring }}</h3>
                    <p>Hosting Expiring (30d)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-server"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Website Management</h3>
            <div class="card-tools ml-auto">
                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                <button wire:click="create" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Add Website
                </button>
                @endif
            </div>
        </div>
        <div class="card-body p-3 bg-light border-bottom">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Search websites..." wire:model.live="searchTerm">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
                @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                <div class="col-md-4 mb-2">
                    <select class="form-control form-control-sm" wire:model.live="filterClient">
                        <option value="">-- All Clients --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2 text-right">
                    <button wire:click="$set('searchTerm', ''); $set('filterClient', '');" class="btn btn-sm btn-outline-secondary shadow-sm">
                        <i class="fas fa-sync mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover text-nowrap mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-top-0">#</th>
                        <th class="border-top-0">Website Details</th>
                        <th class="border-top-0">Client</th>
                        <th class="border-top-0">Expiries</th>
                        <th class="border-top-0">Hosting</th>
                        <th class="border-top-0 text-center">Status</th>
                        <th class="border-top-0 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($websites as $index => $website)
                        <tr>
                            <td>{{ $websites->firstItem() + $index }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $website->name }}</div>
                                @if($website->url)
                                    <a href="{{ $website->url }}" target="_blank" class="small text-primary">
                                        <i class="fas fa-external-link-alt mr-1"></i>{{ Str::limit($website->url, 40) }}
                                    </a>
                                @endif
                                <div class="text-muted small">{{ $website->domain_name }}</div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $website->client->company_name ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $website->client->user->name ?? '' }}</div>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <span class="small text-muted">Domain:</span>
                                    @if($website->domain_expiry_date)
                                        @php
                                            $diff = now()->diffInDays($website->domain_expiry_date, false);
                                            $color = $diff < 0 ? 'text-danger' : ($diff < 30 ? 'text-warning' : 'text-success');
                                        @endphp
                                        <span class="{{ $color }} font-weight-bold small">
                                            {{ $website->domain_expiry_date->format('d M Y') }}
                                            @if($diff < 30 && $diff >= 0) ({{ $diff }}d) @elseif($diff < 0) (Expired) @endif
                                        </span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="small text-muted">SSL:</span>
                                    @if($website->ssl_expiry_date)
                                        @php
                                            $diffSSL = now()->diffInDays($website->ssl_expiry_date, false);
                                            $colorSSL = $diffSSL < 0 ? 'text-danger' : ($diffSSL < 30 ? 'text-warning' : 'text-success');
                                        @endphp
                                        <span class="{{ $colorSSL }} font-weight-bold small">
                                            {{ $website->ssl_expiry_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $website->hosting_provider ?? 'N/A' }}</div>
                                @if($website->hosting_expiry_date)
                                    @php
                                        $diffH = now()->diffInDays($website->hosting_expiry_date, false);
                                        $colorH = $diffH < 0 ? 'text-danger' : ($diffH < 30 ? 'text-warning' : 'text-success');
                                    @endphp
                                    <div class="small {{ $colorH }}">
                                        Exp: {{ $website->hosting_expiry_date->format('d M Y') }}
                                    </div>
                                @endif
                                <div class="text-muted small">{{ $website->server_ip ?? '' }}</div>
                                @if($website->php_version)
                                    <span class="badge badge-info small" style="font-size: 0.65rem;">PHP {{ $website->php_version }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $website->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $website->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="btn-group shadow-sm">
                                    @php
                                        $phone = preg_replace('/[^0-9]/', '', $website->client->phone ?? '');
                                        if($phone && strlen($phone) == 10) $phone = '91' . $phone;
                                        
                                        $clientName = $website->client->company_name ?? ($website->client->user->name ?? 'there');
                                        
                                        $expiryInfo = "";
                                        $isExpired = false;
                                        
                                        if($website->domain_expiry_date && $website->domain_expiry_date < now()) {
                                            $expiryInfo .= "Domain (Expired on " . $website->domain_expiry_date->format('d M Y') . ")";
                                            $isExpired = true;
                                        } elseif($website->domain_expiry_date && $website->domain_expiry_date < now()->addDays(30)) {
                                            $expiryInfo .= "Domain (Expiring on " . $website->domain_expiry_date->format('d M Y') . ")";
                                        }
                                        
                                        if($website->hosting_expiry_date && $website->hosting_expiry_date < now()) {
                                            $expiryInfo .= ($expiryInfo ? " & " : "") . "Hosting (Expired on " . $website->hosting_expiry_date->format('d M Y') . ")";
                                            $isExpired = true;
                                        } elseif($website->hosting_expiry_date && $website->hosting_expiry_date < now()->addDays(30)) {
                                            $expiryInfo .= ($expiryInfo ? " & " : "") . "Hosting (Expiring on " . $website->hosting_expiry_date->format('d M Y') . ")";
                                        }
                                        
                                        $waMessage = "Hi " . $clientName . "! This is Umakant Yadav.\n\nI am writing to notify you regarding your website: *" . $website->name . "* (" . $website->domain_name . ").\n\nYour *" . ($expiryInfo ?: "services") . "* " . ($isExpired ? "have expired" : "are expiring soon") . ". Please process the renewal payment to ensure your website remains online and active.\n\nLet me know if you need the payment details or a renewal invoice!";
                                    @endphp
                                    
                                    @if($phone)
                                    <button type="button" class="btn btn-success btn-sm" title="Notify Expiry via WhatsApp" onclick="handleWaExpiry(this, '{{ $phone }}', `{{ $waMessage }}`)">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    @endif

                                    <button wire:click="edit({{ $website->id }})" class="btn btn-info btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('admin'))
                                    <button wire:click="delete({{ $website->id }})" class="btn btn-danger btn-sm" title="Delete" onclick="confirm('Are you sure you want to delete this website?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-globe fa-3x mb-3 text-light"></i><br/>
                                    No websites found. Click "Add Website" to get started.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($websites->hasPages())
            <div class="card-footer clearfix bg-white">
                <div class="float-right">
                    {{ $websites->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white d-flex flex-column p-0">
                    <div class="d-flex justify-content-between align-items-center w-100 p-3">
                        <h5 class="modal-title font-weight-bold mb-0">
                            <i class="fas {{ $isEditMode ? 'fa-edit' : 'fa-plus' }} mr-2"></i>
                            {{ $isEditMode ? 'Manage Website' : 'Add New Website' }}
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @if($isEditMode)
                    <ul class="nav nav-tabs border-0 px-3">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'details' ? 'active bg-white text-primary border-bottom-0' : 'text-white border-0' }}" href="#" wire:click.prevent="switchTab('details')">Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'history' ? 'active bg-white text-primary border-bottom-0' : 'text-white border-0' }}" href="#" wire:click.prevent="switchTab('history')">Renewal & Payment History</a>
                        </li>
                    </ul>
                    @endif
                </div>
                <div class="modal-body p-0">
                    @if($activeTab == 'details')
                    <div class="p-4">
                        <form>
                            <!-- Existing form fields -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Website Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="name" placeholder="e.g. Corporate Website">
                                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Select Client <span class="text-danger">*</span></label>
                                    <select class="form-control" wire:model="client_id">
                                        <option value="">-- Select Client --</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company_name }} ({{ $client->user->name ?? '' }})</option>
                                        @endforeach
                                    </select>
                                    @error('client_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Main URL</label>
                                    <input type="url" class="form-control" wire:model="url" placeholder="https://example.com">
                                    @error('url') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Domain Name</label>
                                    <input type="text" class="form-control" wire:model="domain_name" placeholder="example.com">
                                    @error('domain_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-calendar-alt mr-2"></i>Expiry Dates</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted">Domain Expiry</label>
                                    <input type="date" class="form-control" wire:model="domain_expiry_date">
                                    @error('domain_expiry_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted">SSL Expiry</label>
                                    <input type="date" class="form-control" wire:model="ssl_expiry_date">
                                    @error('ssl_expiry_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-server mr-2"></i>Hosting & Technical</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Hosting Provider</label>
                                    <input type="text" class="form-control" wire:model="hosting_provider" placeholder="e.g. Hostinger, AWS">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Hosting Expiry</label>
                                    <input type="date" class="form-control" wire:model="hosting_expiry_date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Server IP</label>
                                    <input type="text" class="form-control" wire:model="server_ip" placeholder="1.2.3.4">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">PHP Version</label>
                                    <input type="text" class="form-control" wire:model="php_version" placeholder="8.2">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">CMS / Framework</label>
                                    <input type="text" class="form-control" wire:model="cms" placeholder="WordPress, Laravel, etc.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mt-4">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active_switch" wire:model="is_active">
                                        <label class="custom-control-label font-weight-bold" for="is_active_switch">Active Status</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-user-lock mr-2"></i>Admin Access (Encrypted)</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Admin URL</label>
                                    <input type="url" class="form-control form-control-sm" wire:model="admin_url" placeholder="https://example.com/admin">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Username</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="admin_username">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted small">Password</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="admin_password">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="font-weight-bold text-muted small">Additional Notes</label>
                            <textarea class="form-control" wire:model="notes" rows="3" placeholder="Server details, FTP, etc."></textarea>
                        </div>
                    </form>
                    @elseif($activeTab == 'history')
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0">Renewal History</h6>
                            <button type="button" class="btn btn-xs btn-success" wire:click="toggleRenewalForm">
                                <i class="fas {{ $showRenewalForm ? 'fa-minus' : 'fa-plus' }} mr-1"></i>
                                {{ $showRenewalForm ? 'Cancel' : 'Add Renewal' }}
                            </button>
                        </div>

                        @if($showRenewalForm)
                        <div class="card card-outline card-success mb-4 shadow-sm">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Type</label>
                                            <select class="form-control form-control-sm" wire:model="renewal_type">
                                                <option value="domain">Domain</option>
                                                <option value="hosting">Hosting</option>
                                                <option value="ssl">SSL</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Renewal Date</label>
                                            <input type="date" class="form-control form-control-sm" wire:model="renewal_date">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">New Expiry</label>
                                            <input type="date" class="form-control form-control-sm" wire:model="renewal_new_expiry_date">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Amount</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" class="form-control" wire:model="renewal_amount">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">INR</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Status</label>
                                            <select class="form-control form-control-sm" wire:model="renewal_payment_status">
                                                <option value="Paid">Paid</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Partial">Partial</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Notes</label>
                                            <input type="text" class="form-control form-control-sm" wire:model="renewal_notes" placeholder="Any extra details...">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="d-block">&nbsp;</label>
                                        <button type="button" class="btn btn-sm btn-success btn-block" wire:click="storeRenewal">
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-sm table-striped small">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>New Expiry</th>
                                        <th>Status</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $currentWebsite = \App\Models\Website::find($website_id);
                                    @endphp
                                    @if($currentWebsite)
                                        @forelse($currentWebsite->renewals as $renewal)
                                            <tr>
                                                <td>{{ $renewal->renewal_date->format('d M Y') }}</td>
                                                <td><span class="badge badge-secondary">{{ strtoupper($renewal->type) }}</span></td>
                                                <td>{{ $renewal->currency }} {{ number_format($renewal->amount, 2) }}</td>
                                                <td>{{ $renewal->new_expiry_date->format('d M Y') }}</td>
                                                <td>
                                                    <span class="badge {{ $renewal->payment_status == 'Paid' ? 'badge-success' : 'badge-warning' }}">
                                                        {{ $renewal->payment_status }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <button type="button" class="btn btn-xs btn-danger" wire:click="deleteRenewal({{ $renewal->id }})" onclick="confirm('Delete this record?') || event.stopImmediatePropagation()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">No renewal history found.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" wire:click="closeModal">Close</button>
                    @if($activeTab == 'details')
                    <button type="button" class="btn btn-primary shadow-sm px-4" wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        <i class="fas fa-save mr-2"></i> {{ $isEditMode ? 'Update Website' : 'Save Website' }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        function handleWaExpiry(btnElem, phone, message) {
            const btn = $(btnElem);
            const waUrl = "https://wa.me/" + phone + "?text=" + encodeURIComponent(message);
            
            // Visual feedback
            const originalIconClass = btn.find('i').attr('class');
            btn.find('i').attr('class', 'fas fa-spinner fa-spin');
            btn.prop('disabled', true);

            // Open WhatsApp
            window.open(waUrl, '_blank');
            
            // Reset UI
            setTimeout(() => {
                btn.find('i').attr('class', originalIconClass);
                btn.prop('disabled', false);
                if (typeof toastr !== 'undefined') {
                    toastr.success('Opening WhatsApp... Notification ready.');
                }
            }, 500);
        }
    </script>
</div>
