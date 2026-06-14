<x-admin-layout>
    <x-slot name="header">System Settings</x-slot>
    
    <!-- Settings Tabs -->
    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab">
                        <i class="fas fa-cog mr-2"></i>General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="whatsapp-tab" data-toggle="pill" href="#whatsapp" role="tab">
                        <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="settingsTabContent">
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    @livewire('settings-manager')
                </div>
                <div class="tab-pane fade" id="whatsapp" role="tabpanel">
                    @livewire('whatsapp-settings-manager')
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
