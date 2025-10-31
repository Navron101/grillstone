<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            ['name' => 'pos.access', 'display_name' => 'Access POS', 'module' => 'POS'],
            ['name' => 'pos.manage', 'display_name' => 'Manage POS', 'module' => 'POS'],

            ['name' => 'inventory.access', 'display_name' => 'Access Inventory', 'module' => 'Inventory'],
            ['name' => 'inventory.manage', 'display_name' => 'Manage Inventory', 'module' => 'Inventory'],

            ['name' => 'hr.access', 'display_name' => 'Access HR', 'module' => 'HR'],
            ['name' => 'hr.manage', 'display_name' => 'Manage HR', 'module' => 'HR'],

            ['name' => 'finance.access', 'display_name' => 'Access Finance', 'module' => 'Finance'],
            ['name' => 'finance.manage', 'display_name' => 'Manage Finance', 'module' => 'Finance'],

            ['name' => 'reports.access', 'display_name' => 'Access Reports', 'module' => 'Reports'],
            ['name' => 'reports.manage', 'display_name' => 'Manage Reports', 'module' => 'Reports'],

            ['name' => 'settings.access', 'display_name' => 'Access Settings', 'module' => 'Settings'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Settings', 'module' => 'Settings'],

            ['name' => 'loyalty.access', 'display_name' => 'Access Loyalty', 'module' => 'Loyalty'],
            ['name' => 'loyalty.manage', 'display_name' => 'Manage Loyalty', 'module' => 'Loyalty'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Roles with Permissions
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Full system access',
                'permissions' => Permission::all()->pluck('id')->toArray(), // All permissions
            ],
            [
                'name' => 'director',
                'display_name' => 'Director',
                'description' => 'All except settings',
                'permissions' => Permission::whereNotIn('module', ['Settings'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'POS and Inventory access',
                'permissions' => Permission::whereIn('module', ['POS', 'Inventory'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'procurement',
                'display_name' => 'Procurement',
                'description' => 'Inventory management',
                'permissions' => Permission::where('module', 'Inventory')->pluck('id')->toArray(),
            ],
            [
                'name' => 'hr_officer',
                'display_name' => 'HR Officer',
                'description' => 'Human Resources management',
                'permissions' => Permission::where('module', 'HR')->pluck('id')->toArray(),
            ],
            [
                'name' => 'finance_officer',
                'display_name' => 'Finance Officer',
                'description' => 'Finance management',
                'permissions' => Permission::where('module', 'Finance')->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $roleData) {
            $permissionIds = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Sync permissions
            $role->permissions()->sync($permissionIds);
        }
    }
}
