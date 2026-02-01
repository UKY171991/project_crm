<x-admin-layout>
    <x-slot name="header">
        Profile
    </x-slot>

    <div class="row">
        <!-- Profile Update -->
        <div class="col-md-6">
             <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Profile Information</h3>
                </div>
                <div class="card-body">
                     @include('profile.partials.update-profile-information-form')
                </div>
             </div>
        </div>
        
        <!-- Password Update -->
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Update Password</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
        
        <!-- Delete Account -->
         <div class="col-md-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Delete Account</h3>
                </div>
                <div class="card-body">
                     @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
