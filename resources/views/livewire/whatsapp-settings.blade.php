<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">WhatsApp Integration Settings</h3>
            <div class="card-tools">
                <button wire:click="testConnection" class="btn btn-info btn-sm">
                    <i class="fas fa-plug"></i> Test Connection
                </button>
            </div>
        </div>
        <div class="card-body">
            <form wire:submit="saveSettings">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gateway Type</label>
                            <select class="form-control" wire:model.live="settings.type">
                                <option value="official">Official Meta WhatsApp API</option>
                                <option value="fast2sms">Fast2SMS WhatsApp API</option>
                                <option value="custom">Custom Unofficial Gateway (Without API)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Default Country Code</label>
                            <input type="text" class="form-control" wire:model="settings.default_country_code" placeholder="91">
                            @error('settings.default_country_code') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                @if(($settings['type'] ?? 'official') === 'official' || ($settings['type'] ?? 'official') === 'fast2sms')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>WhatsApp Access Token</label>
                            <input type="password" class="form-control" wire:model="settings.access_token" placeholder="Enter Meta WhatsApp Access Token">
                            @error('settings.access_token') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone Number ID</label>
                            <input type="text" class="form-control" wire:model="settings.phone_number_id" placeholder="Enter Phone Number ID">
                            @error('settings.phone_number_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>API Version</label>
                            <select class="form-control" wire:model="settings.version">
                                <option value="v18.0">v18.0</option>
                                <option value="v17.0">v17.0</option>
                                <option value="v16.0">v16.0</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Template Name</label>
                            <input type="text" class="form-control" wire:model="settings.template_name" placeholder="project_status_update">
                            @error('settings.template_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Language Code</label>
                            <select class="form-control" wire:model="settings.language">
                                <option value="en">English</option>
                                <option value="hi">Hindi</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                    </div>
                    @if(($settings['type'] ?? 'official') === 'official')
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Webhook Verify Token</label>
                            <input type="password" class="form-control" wire:model="settings.webhook_verify_token" placeholder="Enter webhook verification token">
                            @error('settings.webhook_verify_token') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Custom Gateway URL (GET Request)</label>
                            <input type="text" class="form-control" wire:model="settings.custom_gateway_url" placeholder="https://api.yourgateway.com/send?phone={phone}&text={text}">
                            <small class="text-muted">Use <code>{phone}</code> and <code>{text}</code> as placeholders.</small>
                            @error('settings.custom_gateway_url') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="enable_whatsapp" wire:model="settings.enabled">
                        <label class="custom-control-label" for="enable_whatsapp">Enable WhatsApp Notifications</label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Test Message Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Send Test Message</h3>
        </div>
        <div class="card-body">
            <form wire:submit="sendTestMessage">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" class="form-control" wire:model="testPhone" placeholder="91234567890">
                            @error('testPhone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Test Message</label>
                            <input type="text" class="form-control" wire:model="testMessage" placeholder="Enter test message">
                            @error('testMessage') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Send Test Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Connection Status</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert {{ $connectionStatus ? 'alert-success' : 'alert-danger' }}">
                        <h5><i class="fas fa-{{ $connectionStatus ? 'check-circle' : 'times-circle' }}"></i> 
                            WhatsApp API {{ $connectionStatus ? 'Connected' : 'Not Connected' }}
                        </h5>
                        @if($connectionStatus)
                            <p class="mb-0">Your WhatsApp API is working correctly and ready to send messages.</p>
                        @else
                            <p class="mb-0">Please check your configuration and try testing the connection again.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Webhook Information -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Webhook Configuration</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5>Webhook URLs</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th>Verification URL</th>
                                <td><code>{{ url('/webhook/whatsapp/verify') }}</code></td>
                            </tr>
                            <tr>
                                <th>Message URL</th>
                                <td><code>{{ url('/webhook/whatsapp') }}</code></td>
                            </tr>
                        </table>
                    </div>
                    <p class="text-muted">
                        Configure these URLs in your Meta WhatsApp Business account to receive webhook events.
                    </p>
                </div>
            </div>
        </div>
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h3 class="card-title"><i class="fab fa-whatsapp mr-2"></i> WhatsApp Reminder Templates</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4 small">
                <i class="fas fa-info-circle mr-1"></i> These templates are used for the manual WhatsApp reminder button in the Projects List.
                <br>
                Available Placeholders: <code>{greeting}</code>, <code>{name}</code>, <code>{title}</code>, <code>{currency}</code>, <code>{balance}</code>
            </p>
            
            <div class="form-group">
                <label>Pending Project Reminder</label>
                <textarea class="form-control" wire:model="settings.reminder_pending" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label>Running Project Reminder</label>
                <textarea class="form-control" wire:model="settings.reminder_running" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label>Pending Payment Reminder</label>
                <textarea class="form-control" wire:model="settings.reminder_pending_payment" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label>Completed Project Message</label>
                <textarea class="form-control" wire:model="settings.reminder_completed" rows="2"></textarea>
            </div>
            
            <div class="form-group mt-3">
                <button wire:click="saveSettings" class="btn btn-success shadow-sm">
                    <i class="fas fa-save mr-1"></i> Save All Templates
                </button>
            </div>
        </div>
    </div>
</div>
