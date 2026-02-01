<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Roles
        $masterRole = Role::create(['name' => 'Master', 'slug' => 'master']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $clientRole = Role::create(['name' => 'Client', 'slug' => 'client']);
        $userRole = Role::create(['name' => 'User', 'slug' => 'user']);

        // 2. Create Master User
        $master = User::create([
            'name' => 'Master User',
            'email' => 'super@admin.com',
            'password' => Hash::make('password'),
            'role_id' => $masterRole->id,
            'email_verified_at' => now(),
        ]);

        // 3. Create Admin User
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@firm.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'created_by' => $master->id,
            'email_verified_at' => now(),
        ]);

        // 4. Create Client User & Client Profile
        $clientUser = User::create([
            'name' => 'Startup Founder',
            'email' => 'client@startup.com',
            'password' => Hash::make('password'),
            'role_id' => $clientRole->id,
            'created_by' => $admin->id,
            'email_verified_at' => now(),
        ]);

        $clientProfile = Client::create([
            'user_id' => $clientUser->id,
            'company_name' => 'Startup Inc',
            'phone' => '1234567890',
            'address' => '123 Tech Park',
            'status' => 'active',
        ]);

        // 5. Create Normal User
        $normalUser = User::create([
            'name' => 'Developer One',
            'email' => 'dev@firm.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id,
            'created_by' => $admin->id,
            'email_verified_at' => now(),
        ]);

        // 6. Create Sample Project
        Project::create([
            'uuid' => Str::uuid(),
            'client_id' => $clientProfile->id,
            'title' => 'E-Commerce Redesign',
            'description' => 'A complete redesign of the online store.',
            'start_date' => now(),
            'status' => 'Running',
            'created_by' => $clientUser->id,
        ]);
        
        Project::create([
            'uuid' => Str::uuid(),
            'client_id' => $clientProfile->id,
            'title' => 'Mobile App MVP',
            'description' => 'Initial MVP for the mobile app.',
            'start_date' => now()->addDays(2),
            'status' => 'Pending',
            'created_by' => $master->id, // Master created this one
        ]);
    }
}
