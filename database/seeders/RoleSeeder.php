<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard analytics',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'ban users',
            'view flights',
            'create flights',
            'edit flights',
            'delete flights',
            'manage airlines',
            'manage positions',
            'view swaps',
            'approve swaps',
            'manage swaps',
            'view reports',
            'manage reports',
            'view chats',
            'moderate chats',
            'send notifications',
            'manage settings',
            'view audit logs',
            'view system health',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());

        $role = Role::firstOrCreate(['name' => 'crew_manager']);
        $role->givePermissionTo([
            'view dashboard analytics',
            'view users',
            'view flights',
            'view swaps',
            'approve swaps',
            'view reports',
            'manage reports',
            'view chats',
        ]);

        $role = Role::firstOrCreate(['name' => 'hr_manager']);
        $role->givePermissionTo([
            'view dashboard analytics',
            'view users',
            'create users',
            'edit users',
            'ban users',
            'view reports',
            'manage reports',
            'view audit logs',
        ]);

        $role = Role::firstOrCreate(['name' => 'operations_manager']);
        $role->givePermissionTo([
            'view dashboard analytics',
            'view flights',
            'create flights',
            'edit flights',
            'view swaps',
            'approve swaps',
            'manage swaps',
            'manage airlines',
            'manage positions',
            'view system health',
        ]);

        $role = Role::firstOrCreate(['name' => 'support_moderator']);
        $role->givePermissionTo([
            'view dashboard analytics',
            'view users',
            'ban users',
            'view reports',
            'manage reports',
            'view chats',
            'moderate chats',
            'send notifications',
        ]);

        $role = Role::firstOrCreate(['name' => 'data_analyst']);
        $role->givePermissionTo([
            'view dashboard analytics',
            'view audit logs',
            'view system health',
        ]);

        $role = Role::firstOrCreate(['name' => 'purser']);
        $role->givePermissionTo([
            'view flights',
            'view swaps',
        ]);

        $role = Role::firstOrCreate(['name' => 'flight_attendant']);
        $role->givePermissionTo([
            'view flights',
            'view swaps',
        ]);
    }
}
