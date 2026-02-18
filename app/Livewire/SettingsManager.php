<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use App\Models\Currency;
use Illuminate\Support\Facades\Storage;

class SettingsManager extends Component
{
    use WithFileUploads;

    // General Settings
    public $system_title;
    public $new_logo;
    public $new_favicon;
    public $current_logo;
    public $current_favicon;
    public $registration_enabled;

    // Cron Settings
    public $cron_email;
    public $cron_key;

    // Mail Settings
    public $mail_mailer = 'smtp';
    public $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption, $mail_from_address, $mail_from_name;

    // Currency Settings
    public $currencies;
    public $currency_code, $currency_name, $currency_symbol;
    public $editing_currency_id = null;
    public $showCurrencyModal = false;

    public function mount()
    {
        $this->system_title = Setting::get('system_title', 'Project Management System');
        $this->current_logo = Setting::get('system_logo');
        $this->current_favicon = Setting::get('system_favicon');
        $this->registration_enabled = Setting::get('registration_enabled', true) == '1';
        $this->cron_email = Setting::get('cron_email', 'uky171991@gmail.com');
        $this->cron_key = Setting::get('cron_key', 'crm_tasks_cron_2026');

        // Mail settings from database
        $this->mail_mailer = Setting::get('mail_mailer', 'smtp');
        $this->mail_host = Setting::get('mail_host');
        $this->mail_port = Setting::get('mail_port', '587');
        $this->mail_username = Setting::get('mail_username');
        $this->mail_password = Setting::get('mail_password');
        $this->mail_encryption = Setting::get('mail_encryption', 'tls');
        $this->mail_from_address = Setting::get('mail_from_address');
        $this->mail_from_name = Setting::get('mail_from_name', $this->system_title);

        $this->loadCurrencies();
    }

    public function loadCurrencies()
    {
        $this->currencies = Currency::all();
    }

    public function save()
    {
        $this->validate([
            'system_title' => 'required|string|max:255',
            'new_logo' => 'nullable|image|max:2048', // 2MB
            'new_favicon' => 'nullable|image|max:1024', // 1MB
            'cron_email' => 'required|email',
            'cron_key' => 'required|string',
            'mail_mailer' => 'required|in:smtp,sendmail,log',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        if ($this->mail_mailer === 'smtp') {
            $this->validate([
                'mail_host' => 'required',
                'mail_port' => 'required|numeric',
            ]);
        }

        Setting::set('system_title', $this->system_title);

        if ($this->new_logo) {
            if ($this->current_logo && Storage::disk('public')->exists($this->current_logo)) {
                Storage::disk('public')->delete($this->current_logo);
            }
            $logoPath = $this->new_logo->store('settings', 'public');
            Setting::set('system_logo', $logoPath);
            $this->current_logo = $logoPath;
            $this->new_logo = null;
        }

        if ($this->new_favicon) {
            if ($this->current_favicon && Storage::disk('public')->exists($this->current_favicon)) {
                Storage::disk('public')->delete($this->current_favicon);
            }
            $faviconPath = $this->new_favicon->store('settings', 'public');
            Setting::set('system_favicon', $faviconPath);
            $this->current_favicon = $faviconPath;
            $this->new_favicon = null;
        }

        Setting::set('registration_enabled', $this->registration_enabled);
        Setting::set('cron_email', $this->cron_email);
        Setting::set('cron_key', $this->cron_key);

        // Save Mail Settings
        Setting::set('mail_mailer', $this->mail_mailer);
        Setting::set('mail_host', $this->mail_host);
        Setting::set('mail_port', $this->mail_port);
        Setting::set('mail_username', $this->mail_username);
        Setting::set('mail_password', $this->mail_password);
        Setting::set('mail_encryption', $this->mail_encryption);
        Setting::set('mail_from_address', $this->mail_from_address);
        Setting::set('mail_from_name', $this->mail_from_name);

        session()->flash('success', 'General settings updated successfully.');
        $this->dispatch('settingsUpdated');
    }

    // Currency CRUD
    public function openCurrencyModal($id = null)
    {
        $this->resetValidation();
        if ($id) {
            $currency = Currency::find($id);
            $this->editing_currency_id = $id;
            $this->currency_code = $currency->code;
            $this->currency_name = $currency->name;
            $this->currency_symbol = $currency->symbol;
        } else {
            $this->editing_currency_id = null;
            $this->currency_code = '';
            $this->currency_name = '';
            $this->currency_symbol = '';
        }
        $this->showCurrencyModal = true;
    }

    public function closeCurrencyModal()
    {
        $this->showCurrencyModal = false;
    }

    public function saveCurrency()
    {
        $this->validate([
            'currency_code' => 'required|string|max:10|unique:currencies,code,' . $this->editing_currency_id,
            'currency_name' => 'required|string|max:255',
            'currency_symbol' => 'required|string|max:10',
        ]);

        if ($this->editing_currency_id) {
            Currency::find($this->editing_currency_id)->update([
                'code' => strtoupper($this->currency_code),
                'name' => $this->currency_name,
                'symbol' => $this->currency_symbol,
            ]);
            session()->flash('currency_success', 'Currency updated successfully.');
        } else {
            Currency::create([
                'code' => strtoupper($this->currency_code),
                'name' => $this->currency_name,
                'symbol' => $this->currency_symbol,
            ]);
            session()->flash('currency_success', 'Currency added successfully.');
        }

        $this->loadCurrencies();
        $this->closeCurrencyModal();
    }

    public function deleteCurrency($id)
    {
        Currency::destroy($id);
        $this->loadCurrencies();
        session()->flash('currency_success', 'Currency deleted successfully.');
    }

    public function toggleCurrencyStatus($id)
    {
        $currency = Currency::find($id);
        $currency->update(['is_active' => !$currency->is_active]);
        $this->loadCurrencies();
    }

    public function render()
    {
        return view('livewire.settings-manager');
    }
}
