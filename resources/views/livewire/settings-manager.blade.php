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

            <!-- Currency Settings -->
            <div class="card card-info mt-4">
                <div class="card-header">
                    <h3 class="card-title">Currency Settings</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-light" wire:click="openCurrencyModal">
                            <i class="fas fa-plus"></i> Add Currency
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    @if(session()->has('currency_success'))
                        <div class="alert alert-success mx-3 mt-3 py-2 small">
                            {{ session('currency_success') }}
                        </div>
                    @endif
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th style="width: 5%">Sr.</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Symbol</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currencies as $currency)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $currency->code }}</strong></td>
                                    <td>{{ $currency->name }}</td>
                                    <td>{{ $currency->symbol }}</td>
                                    <td>
                                        <button wire:click="toggleCurrencyStatus({{ $currency->id }})" 
                                                class="btn btn-xs {{ $currency->is_active ? 'btn-success' : 'btn-secondary' }}">
                                            {{ $currency->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="text-right">
                                        <button wire:click="openCurrencyModal({{ $currency->id }})" class="btn btn-xs btn-info">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="deleteCurrency({{ $currency->id }})" 
                                                class="btn btn-xs btn-danger" 
                                                onclick="confirm('Are you sure you want to delete this currency?') || event.stopImmediatePropagation()">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No currencies added.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Preview</h3>
                </div>
                <div class="card-body text-center">
                    <h5>Current Logo</h5>
                    <div class="p-4 bg-light rounded mb-3 shadow-sm border">
                        @if($new_logo)
                            <img src="{{ $new_logo->temporaryUrl() }}" style="max-width: 100%; max-height: 100px;">
                        @elseif($current_logo)
                            <img src="{{ asset('storage/' . $current_logo) }}" style="max-width: 100%; max-height: 100px;">
                        @else
                            <div class="text-muted italic">No Logo Set</div>
                        @endif
                    </div>

                    <h5>Current Favicon</h5>
                    <div class="p-4 bg-light rounded shadow-sm border">
                        @if($new_favicon)
                            <img src="{{ $new_favicon->temporaryUrl() }}" style="max-width: 32px; max-height: 32px;">
                        @elseif($current_favicon)
                            <img src="{{ asset('storage/' . $current_favicon) }}" style="max-width: 32px; max-height: 32px;">
                        @else
                            <div class="text-muted italic">No Favicon Set</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currency Modal -->
    @if($showCurrencyModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-info py-2">
                    <h5 class="modal-title font-weight-bold text-white">{{ $editing_currency_id ? 'Edit' : 'Add' }} Currency</h5>
                    <button type="button" class="close text-white" wire:click="closeCurrencyModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-4">
                    <form wire:submit.prevent="saveCurrency">
                        <div class="form-group">
                            <label>Currency Code (e.g. USD)</label>
                            <input type="text" class="form-control" wire:model="currency_code" placeholder="USD">
                            @error('currency_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Currency Name</label>
                            <input type="text" class="form-control" wire:model="currency_name" placeholder="US Dollar">
                            @error('currency_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>Symbol</label>
                            <input type="text" class="form-control" wire:model="currency_symbol" placeholder="$">
                            @error('currency_symbol') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closeCurrencyModal">Cancel</button>
                    <button type="button" class="btn btn-info btn-sm shadow-sm" wire:click="saveCurrency">
                        {{ $editing_currency_id ? 'Update Currency' : 'Add Currency' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
