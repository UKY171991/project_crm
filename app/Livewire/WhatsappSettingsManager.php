<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Artisan;

class WhatsappSettingsManager extends Component
{
    public $settings = [
        'access_token' => '',
        'phone_number_id' => '',
        'version' => 'v18.0',
        'webhook_verify_token' => '',
        'template_name' => 'praposal',
        'template_id' => '1014519774407056',
        'sender_number' => '+919453619260',
        'payment_template_name' => 'project_payment_status',
        'proposal_template_name' => '23233',
        'use_default_only' => true,
        'template_pending_to_running' => 'project_status_pending_to_running',
        'template_running_to_pending_payment' => 'project_status_running_to_pending_payment',
        'template_pending_payment_to_completed' => 'project_status_pending_payment_to_completed',
        'template_pending_to_canceled' => 'project_status_pending_to_canceled',
        'template_running_to_canceled' => 'project_status_running_to_canceled',
        'template_pending_payment_to_canceled' => 'project_status_pending_payment_to_canceled',
        'template_canceled_to_pending' => 'project_status_canceled_to_pending',
        'template_canceled_to_running' => 'project_status_canceled_to_running',
        'language' => 'en',
        'default_country_code' => '91',
        'enabled' => false,
        'type' => 'official',
        'custom_gateway_url' => '',
        'reminder_pending' => '{greeting} {name}, your project {title} is currently pending. We will start soon.',
        'reminder_running' => '{greeting} {name}, your project {title} is currently running. We are working on it!',
        'reminder_pending_payment' => '{greeting} {name}, your project {title} is completed. Please clear the balance of {currency} {balance}.',
        'reminder_completed' => '{greeting} {name}, your project {title} is completed. Thank you!',
    ];

    public $customTemplates = [];
    public $newTemplateName = '';
    public $newTemplateCode = '';
    public $newTemplateLanguage = 'en';

    // Test send template properties
    public $testMessageType = 'text'; // text or template
    public $selectedTemplateCode = '';
    public $testTemplateVariables = '';

    public $testPhone = '';
    public $testMessage = 'Test message from CRM system';
    public $connectionStatus = false;

    public function rules()
    {
        $rules = [
            'settings.type' => 'required|string|in:official,fast2sms,custom',
            'settings.default_country_code' => 'required|string|max:5',
        ];

        if ($this->settings['type'] === 'official' || $this->settings['type'] === 'fast2sms') {
            $rules['settings.access_token'] = 'required|string';
            $rules['settings.phone_number_id'] = 'required|string';
            $rules['settings.template_name'] = 'required|string';
            $rules['settings.template_id'] = 'required|string';
            $rules['settings.sender_number'] = 'required|string';
            $rules['settings.proposal_template_name'] = 'required|string';
            
            // Make others optional so we don't trigger validation errors for hidden fields
            $rules['settings.version'] = 'nullable|string';
            $rules['settings.webhook_verify_token'] = 'nullable|string';
            $rules['settings.payment_template_name'] = 'nullable|string';
            $rules['settings.use_default_only'] = 'nullable|boolean';
            $rules['settings.language'] = 'nullable|string';
            $rules['settings.template_pending_to_running'] = 'nullable|string';
            $rules['settings.template_running_to_pending_payment'] = 'nullable|string';
            $rules['settings.template_pending_payment_to_completed'] = 'nullable|string';
            $rules['settings.template_pending_to_canceled'] = 'nullable|string';
            $rules['settings.template_running_to_canceled'] = 'nullable|string';
            $rules['settings.template_pending_payment_to_canceled'] = 'nullable|string';
            $rules['settings.template_canceled_to_pending'] = 'nullable|string';
            $rules['settings.template_canceled_to_running'] = 'nullable|string';
        } else {
            $rules['settings.custom_gateway_url'] = 'required|url';
            $rules['settings.access_token'] = 'nullable|string';
            $rules['settings.phone_number_id'] = 'nullable|string';
            $rules['settings.template_name'] = 'nullable|string';
            $rules['settings.template_id'] = 'nullable|string';
            $rules['settings.sender_number'] = 'nullable|string';
            $rules['settings.proposal_template_name'] = 'nullable|string';
            $rules['settings.version'] = 'nullable|string';
            $rules['settings.webhook_verify_token'] = 'nullable|string';
            $rules['settings.payment_template_name'] = 'nullable|string';
            $rules['settings.use_default_only'] = 'nullable|boolean';
            $rules['settings.template_pending_to_running'] = 'nullable|string';
            $rules['settings.template_running_to_pending_payment'] = 'nullable|string';
            $rules['settings.template_pending_payment_to_completed'] = 'nullable|string';
            $rules['settings.template_pending_to_canceled'] = 'nullable|string';
            $rules['settings.template_running_to_canceled'] = 'nullable|string';
            $rules['settings.template_pending_payment_to_canceled'] = 'nullable|string';
            $rules['settings.template_canceled_to_pending'] = 'nullable|string';
            $rules['settings.template_canceled_to_running'] = 'nullable|string';
            $rules['settings.language'] = 'nullable|string';
        }

        return $rules;
    }

    protected $messages = [
        'settings.access_token.required' => 'Access token is required',
        'settings.phone_number_id.required' => 'Phone number ID is required',
        'settings.webhook_verify_token.required' => 'Webhook verify token is required',
    ];

    public function mount()
    {
        $this->loadSettings();
        $this->checkConnection();
    }

    public function loadSettings()
    {
        // Load settings from config, which falls back to env or defaults
        $this->settings = [
            'access_token' => config('services.whatsapp.access_token', ''),
            'phone_number_id' => config('services.whatsapp.phone_number_id', ''),
            'version' => config('services.whatsapp.version', 'v18.0'),
            'webhook_verify_token' => config('services.whatsapp.webhook_verify_token', ''),
            'template_name' => config('services.whatsapp.template_name', 'praposal'),
            'template_id' => config('services.whatsapp.template_id', '1014519774407056'),
            'sender_number' => config('services.whatsapp.sender_number', '+919453619260'),
            'payment_template_name' => config('services.whatsapp.payment_template_name', 'project_payment_status'),
            'proposal_template_name' => config('services.whatsapp.proposal_template_name', '23233'),
            'use_default_only' => filter_var(config('services.whatsapp.use_default_only', true), FILTER_VALIDATE_BOOLEAN),
            'template_pending_to_running' => config('services.whatsapp.template_pending_to_running', 'project_status_pending_to_running'),
            'template_running_to_pending_payment' => config('services.whatsapp.template_running_to_pending_payment', 'project_status_running_to_pending_payment'),
            'template_pending_payment_to_completed' => config('services.whatsapp.template_pending_payment_to_completed', 'project_status_pending_payment_to_completed'),
            'template_pending_to_canceled' => config('services.whatsapp.template_pending_to_canceled', 'project_status_pending_to_canceled'),
            'template_running_to_canceled' => config('services.whatsapp.template_running_to_canceled', 'project_status_running_to_canceled'),
            'template_pending_payment_to_canceled' => config('services.whatsapp.template_pending_payment_to_canceled', 'project_status_pending_payment_to_canceled'),
            'template_canceled_to_pending' => config('services.whatsapp.template_canceled_to_pending', 'project_status_canceled_to_pending'),
            'template_canceled_to_running' => config('services.whatsapp.template_canceled_to_running', 'project_status_canceled_to_running'),
            'language' => config('services.whatsapp.language', 'en'),
            'default_country_code' => config('services.whatsapp.default_country_code', '91'),
            'enabled' => filter_var(config('services.whatsapp.enabled', false), FILTER_VALIDATE_BOOLEAN),
            'type' => config('services.whatsapp.type', 'official'),
            'custom_gateway_url' => config('services.whatsapp.custom_gateway_url', ''),
            'reminder_pending' => config('services.whatsapp.reminder_pending', 'Hello {name}, your project {title} is currently pending. We will start soon.'),
            'reminder_running' => config('services.whatsapp.reminder_running', 'Hello {name}, your project {title} is currently running. We are working on it!'),
            'reminder_pending_payment' => config('services.whatsapp.reminder_pending_payment', 'Hello {name}, your project {title} is completed. Please clear the balance of {currency} {balance}.'),
            'reminder_completed' => config('services.whatsapp.reminder_completed', 'Hello {name}, your project {title} is completed. Thank you!'),
        ];

        // If config is empty but env is not (sometimes config isn't cached but env is), fallback to env
        if (empty($this->settings['access_token'])) {
             $this->settings['access_token'] = env('WHATSAPP_ACCESS_TOKEN', '');
        }
        if (empty($this->settings['phone_number_id'])) {
             $this->settings['phone_number_id'] = env('WHATSAPP_PHONE_NUMBER_ID', '');
        }
        if (empty($this->settings['template_id'])) {
             $this->settings['template_id'] = env('WHATSAPP_TEMPLATE_ID', '1014519774407056');
        }
        if (empty($this->settings['sender_number'])) {
             $this->settings['sender_number'] = env('WHATSAPP_SENDER_NUMBER', '+919453619260');
        }

        // Load dynamic templates
        $this->customTemplates = json_decode(\App\Models\Setting::get('whatsapp_templates', '[]'), true);
        if (!is_array($this->customTemplates)) {
            $this->customTemplates = [];
        }
    }

    public function saveSettings()
    {
        $this->validate();

        try {
            // Update .env file
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            $envUpdates = [
                'WHATSAPP_ACCESS_TOKEN' => $this->settings['access_token'],
                'WHATSAPP_PHONE_NUMBER_ID' => $this->settings['phone_number_id'],
                'WHATSAPP_API_VERSION' => $this->settings['version'],
                'WHATSAPP_WEBHOOK_VERIFY_TOKEN' => $this->settings['webhook_verify_token'],
                'WHATSAPP_TEMPLATE_NAME' => $this->settings['template_name'],
                'WHATSAPP_TEMPLATE_ID' => $this->settings['template_id'],
                'WHATSAPP_SENDER_NUMBER' => $this->settings['sender_number'],
                'WHATSAPP_PAYMENT_TEMPLATE_NAME' => $this->settings['payment_template_name'],
                'WHATSAPP_PROPOSAL_TEMPLATE_NAME' => $this->settings['proposal_template_name'],
                'WHATSAPP_USE_DEFAULT_ONLY' => $this->settings['use_default_only'] ? 'true' : 'false',
                'WHATSAPP_TEMPLATE_PENDING_TO_RUNNING' => $this->settings['template_pending_to_running'],
                'WHATSAPP_TEMPLATE_RUNNING_TO_PENDING_PAYMENT' => $this->settings['template_running_to_pending_payment'],
                'WHATSAPP_TEMPLATE_PENDING_PAYMENT_TO_COMPLETED' => $this->settings['template_pending_payment_to_completed'],
                'WHATSAPP_TEMPLATE_PENDING_TO_CANCELED' => $this->settings['template_pending_to_canceled'],
                'WHATSAPP_TEMPLATE_RUNNING_TO_CANCELED' => $this->settings['template_running_to_canceled'],
                'WHATSAPP_TEMPLATE_PENDING_PAYMENT_TO_CANCELED' => $this->settings['template_pending_payment_to_canceled'],
                'WHATSAPP_TEMPLATE_CANCELED_TO_PENDING' => $this->settings['template_canceled_to_pending'],
                'WHATSAPP_TEMPLATE_CANCELED_TO_RUNNING' => $this->settings['template_canceled_to_running'],
                'WHATSAPP_LANGUAGE' => $this->settings['language'],
                'WHATSAPP_DEFAULT_COUNTRY_CODE' => $this->settings['default_country_code'],
                'WHATSAPP_ENABLED' => $this->settings['enabled'] ? 'true' : 'false',
                'WHATSAPP_TYPE' => $this->settings['type'],
                'WHATSAPP_CUSTOM_GATEWAY_URL' => '"' . $this->settings['custom_gateway_url'] . '"',
                'WHATSAPP_REMINDER_PENDING' => '"' . $this->settings['reminder_pending'] . '"',
                'WHATSAPP_REMINDER_RUNNING' => '"' . $this->settings['reminder_running'] . '"',
                'WHATSAPP_REMINDER_PENDING_PAYMENT' => '"' . $this->settings['reminder_pending_payment'] . '"',
                'WHATSAPP_REMINDER_COMPLETED' => '"' . $this->settings['reminder_completed'] . '"',
            ];

            foreach ($envUpdates as $key => $value) {
                $pattern = "/^{$key}=.*/m";
                $replacement = "{$key}={$value}";
                
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }

            file_put_contents($envPath, $envContent);

            // Save dynamic templates to DB
            \App\Models\Setting::set('whatsapp_templates', json_encode($this->customTemplates));

            // Clear configuration cache
            Artisan::call('config:clear');

            session()->flash('success', 'WhatsApp settings saved successfully!');
            
            // Re-check connection
            $this->checkConnection();
            
        } catch (\Exception $e) {
            Log::error('Failed to save WhatsApp settings', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    public function addCustomTemplate()
    {
        $this->validate([
            'newTemplateName' => 'required|string|max:100',
            'newTemplateCode' => 'required|string|max:100',
            'newTemplateLanguage' => 'required|string|max:10',
        ]);

        $this->customTemplates[] = [
            'name' => $this->newTemplateName,
            'code' => $this->newTemplateCode,
            'language' => $this->newTemplateLanguage,
        ];

        \App\Models\Setting::set('whatsapp_templates', json_encode($this->customTemplates));

        $this->newTemplateName = '';
        $this->newTemplateCode = '';
        $this->newTemplateLanguage = 'en';

        session()->flash('success', 'Template added successfully!');
    }

    public function deleteCustomTemplate($index)
    {
        if (isset($this->customTemplates[$index])) {
            unset($this->customTemplates[$index]);
            $this->customTemplates = array_values($this->customTemplates);
            \App\Models\Setting::set('whatsapp_templates', json_encode($this->customTemplates));
            session()->flash('success', 'Template deleted successfully!');
        }
    }

    public function testConnection()
    {
        try {
            // Use current settings (unsaved or recently updated)
            $whatsappService = new WhatsAppService($this->settings);
            $this->connectionStatus = $whatsappService->testConnection();
            
            if ($this->connectionStatus) {
                session()->flash('success', 'WhatsApp API connection successful!');
            } else {
                session()->flash('error', 'WhatsApp API connection failed. Please check your credentials or network.');
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp connection test failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Connection test failed: ' . $e->getMessage());
            $this->connectionStatus = false;
        }
    }
 
    public function checkConnection()
    {
        try {
            if (!empty($this->settings['access_token']) && !empty($this->settings['phone_number_id'])) {
                $whatsappService = new WhatsAppService($this->settings);
                $this->connectionStatus = $whatsappService->testConnection();
            } else {
                $this->connectionStatus = false;
            }
        } catch (\Exception $e) {
            $this->connectionStatus = false;
        }
    }

    public function sendTestMessage()
    {
        if ($this->testMessageType === 'template') {
            $this->validate([
                'testPhone' => 'required|string',
                'selectedTemplateCode' => 'required|string',
            ]);

            try {
                $whatsappService = new WhatsAppService($this->settings);
                
                $variables = [];
                if (!empty($this->testTemplateVariables)) {
                    $variables = explode(',', $this->testTemplateVariables);
                }
                
                $lang = 'en';
                foreach ($this->customTemplates as $tmpl) {
                    if ($tmpl['code'] == $this->selectedTemplateCode) {
                        $lang = $tmpl['language'];
                        break;
                    }
                }

                $success = $whatsappService->sendTemplateMessage(
                    $this->testPhone,
                    $this->selectedTemplateCode,
                    $lang,
                    $variables
                );

                if ($success) {
                    session()->flash('success', 'Test template message sent successfully!');
                } else {
                    session()->flash('error', 'Failed to send test template message. Check your template variables or phone number.');
                }
            } catch (\Exception $e) {
                Log::error('Test template message failed', ['error' => $e->getMessage()]);
                session()->flash('error', 'Test template message failed: ' . $e->getMessage());
            }
        } else {
            $this->validate([
                'testPhone' => 'required|string',
                'testMessage' => 'required|string',
            ]);

            try {
                $whatsappService = new WhatsAppService($this->settings);
                $success = $whatsappService->sendTextMessage($this->testPhone, $this->testMessage);
                
                if ($success) {
                    session()->flash('success', 'Test message sent successfully!');
                } else {
                    session()->flash('error', 'Failed to send test message. Check your template approval or phone number.');
                }
            } catch (\Exception $e) {
                Log::error('Test message failed', ['error' => $e->getMessage()]);
                session()->flash('error', 'Test message failed: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.whatsapp-settings');
    }
}
