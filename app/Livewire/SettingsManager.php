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
        ]);

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
