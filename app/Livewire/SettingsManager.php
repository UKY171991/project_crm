<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsManager extends Component
{
    use WithFileUploads;

    public $system_title;
    public $new_logo;
    public $new_favicon;
    public $current_logo;
    public $current_favicon;

    public function mount()
    {
        $this->system_title = Setting::get('system_title', 'Project Management System');
        $this->current_logo = Setting::get('system_logo');
        $this->current_favicon = Setting::get('system_favicon');
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

        session()->flash('success', 'Settings updated successfully.');
        
        // We might want to dispatch an event to refresh other components or the entire page
        // Since layout depends on this, a refresh might be needed or just let the user see it next time.
        // Actually, Livewire 3 can handle events.
        $this->dispatch('settingsUpdated');
    }

    public function render()
    {
        return view('livewire.settings-manager');
    }
}
