<x-admin-layout>
    <x-slot name="header">Project Details</x-slot>
    @livewire('project-detail-manager', ['project' => $project])
</x-admin-layout>
