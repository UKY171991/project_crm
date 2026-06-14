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
        'template_name' => 'project_status_update',
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
            $rules['settings.version'] = 'required|string';
            $rules['settings.template_name'] = 'required|string';
            $rules['settings.language'] = 'required|string';
            
            if ($this->settings['type'] === 'official') {
                $rules['settings.webhook_verify_token'] = 'required|string';
            } else {
                $rules['settings.webhook_verify_token'] = 'nullable|string';
            }
        } else {
            $rules['settings.custom_gateway_url'] = 'required|url';
            $rules['settings.access_token'] = 'nullable|string';
            $rules['settings.phone_number_id'] = 'nullable|string';
            $rules['settings.version'] = 'nullable|string';
            $rules['settings.webhook_verify_token'] = 'nullable|string';
            $rules['settings.template_name'] = 'nullable|string';
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
            'template_name' => config('services.whatsapp.template_name', 'project_status_update'),
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

    public function render()
    {
        return view('livewire.whatsapp-settings');
    }
}
