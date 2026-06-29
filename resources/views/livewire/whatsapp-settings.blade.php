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
                            <label>WhatsApp Access Token (API Key)</label>
                            <input type="password" class="form-control" wire:model="settings.access_token" placeholder="Enter WABA Access Token">
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
                            <label>Template Name</label>
                            <input type="text" class="form-control" wire:model="settings.template_name" placeholder="Enter WABA Template Name (e.g. praposal)">
                            @error('settings.template_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Template ID</label>
                            <input type="text" class="form-control" wire:model="settings.template_id" placeholder="Enter WABA Template ID (e.g. 1014519774407056)">
                            @error('settings.template_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sender Number</label>
                            <input type="text" class="form-control" wire:model="settings.sender_number" placeholder="Enter Sender Number (e.g. +919453619260)">
                            @error('settings.sender_number') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Message ID</label>
                            <input type="text" class="form-control" wire:model="settings.proposal_template_name" placeholder="Enter Fast2SMS Message ID (e.g. 23233)">
                            @error('settings.proposal_template_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
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

    <!-- Templates Directory Section -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i> WhatsApp Templates Directory</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Template Code / ID</th>
                            <th>Language</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customTemplates as $index => $tmpl)
                            <tr>
                                <td>{{ $tmpl['name'] }}</td>
                                <td><code>{{ $tmpl['code'] }}</code></td>
                                <td><span class="badge badge-secondary">{{ $tmpl['language'] }}</span></td>
                                <td>
                                    <button type="button" wire:click="deleteCustomTemplate({{ $index }})" class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No custom templates added yet. Add your first template below!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <hr>

            <h5>Add New Custom Template</h5>
            <form wire:submit.prevent="addCustomTemplate">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Template Name / Label</label>
                            <input type="text" class="form-control" wire:model="newTemplateName" placeholder="e.g. Welcome Message">
                            @error('newTemplateName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Template ID / Code</label>
                            <input type="text" class="form-control" wire:model="newTemplateCode" placeholder="e.g. 12345 or project_welcome">
                            @error('newTemplateCode') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Language</label>
                            <select class="form-control" wire:model="newTemplateLanguage">
                                <option value="en">English</option>
                                <option value="hi">Hindi</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                            </select>
                            @error('newTemplateLanguage') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-plus"></i> Add Template
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Test Message Section -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i> Send Test Message</h3>
        </div>
        <div class="card-body">
            <form wire:submit="sendTestMessage">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" class="form-control" wire:model="testPhone" placeholder="91234567890">
                            @error('testPhone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Message Type</label>
                            <select class="form-control" wire:model.live="testMessageType">
                                <option value="text">Plain Text Message</option>
                                <option value="template">Template Message</option>
                            </select>
                        </div>
                    </div>
                    @if($testMessageType === 'template')
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Select Template</label>
                                <select class="form-control" wire:model="selectedTemplateCode">
                                    <option value="">-- Choose Template --</option>
                                    @foreach($customTemplates as $tmpl)
                                        <option value="{{ $tmpl['code'] }}">{{ $tmpl['name'] }} ({{ $tmpl['code'] }})</option>
                                    @endforeach
                                </select>
                                @error('selectedTemplateCode') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @else
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Test Message</label>
                                <input type="text" class="form-control" wire:model="testMessage" placeholder="Enter test message">
                                @error('testMessage') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                </div>

                @if($testMessageType === 'template')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Template Variables (Comma-separated, order matters)</label>
                                <input type="text" class="form-control" wire:model="testTemplateVariables" placeholder="e.g. Var1, Var2, Var3">
                                <small class="text-muted">Enter variables for your template in order, separated by commas.</small>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane mr-1"></i> Send Test Message
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
        <div class="card-header bg-dark text-white">
            <h3 class="card-title"><i class="fas fa-link mr-2"></i> Webhook Configuration</h3>
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
                    <p class="text-muted mb-4">
                        Configure these URLs in your Meta WhatsApp Business account to receive webhook events.
                    </p>

                    <div class="alert alert-info shadow-sm">
                        <h5><i class="fas fa-info-circle mr-2"></i> Meta Webhook Setup Guide</h5>
                        <p class="mb-2">Meta (Facebook) requires a secure, publicly accessible HTTPS URL to deliver webhook events. Since localhost URLs are private and HTTP-only, they will not work directly. Follow these steps to set up webhooks for development:</p>
                        <ol class="pl-3 mb-2">
                            <li class="mb-2"><strong>Expose Local Host:</strong> Use a public tunneling tool like <strong>ngrok</strong> or <strong>expose</strong>:
                                <code class="d-block bg-dark text-white p-2 rounded mt-1">ngrok http 8000</code>
                            </li>
                            <li class="mb-2"><strong>Get HTTPS URL:</strong> Copy the secure public URL generated by the tunneling tool (e.g. <code>https://abc1-23-45-67.ngrok-free.app</code>).</li>
                            <li class="mb-2"><strong>Configure Meta Developer Dashboard:</strong>
                                <ul>
                                    <li>Navigate to your <strong>Meta App &gt; WhatsApp &gt; Configuration</strong>.</li>
                                    <li>Under <strong>Callback URL</strong>, paste your public URL appended with the webhook path, for example: <br><code>https://abc1-23-45-67.ngrok-free.app/webhook/whatsapp</code></li>
                                    <li>Under <strong>Verify Token</strong>, enter the exact value of the <strong>Webhook Verify Token</strong> configured in the settings above.</li>
                                </ul>
                            </li>
                            <li><strong>Subscribe to Events:</strong> Click <strong>Manage</strong> next to Webhooks, and subscribe to the <code>messages</code> field to receive incoming messages.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
