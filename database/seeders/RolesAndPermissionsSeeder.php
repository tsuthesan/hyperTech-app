<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $permissions = [
            // Dashboard
            'dashboard.view',

            // User and authorization management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.manage',

            // Products and inventory
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'categories.manage',
            'suppliers.manage',
            'stock.view',
            'stock.receive',
            'stock.adjust',

            // Customers and sales
            'customers.view',
            'customers.create',
            'customers.edit',
            'pos.access',
            'sales.create',
            'sales.view',
            'sales.cancel',
            'invoices.view',
            'invoices.print',

            // Payments and accounts
            'payments.view',
            'payments.record',
            'payments.refund',
            'accounts.view',
            'reports.sales',
            'reports.financial',
            'reports.inventory',

            // System
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $rolePermissions = [
            'admin' => $permissions,

            'cashier' => [
                'dashboard.view',
                'products.view',
                'customers.view',
                'customers.create',
                'customers.edit',
                'pos.access',
                'sales.create',
                'sales.view',
                'invoices.view',
                'invoices.print',
                'payments.view',
                'payments.record',
            ],

            'accountant' => [
                'dashboard.view',
                'customers.view',
                'products.view',
                'stock.view',
                'sales.view',
                'invoices.view',
                'payments.view',
                'payments.record',
                'accounts.view',
                'reports.sales',
                'reports.financial',
                'reports.inventory',
            ],

            'inventory staff' => [
                'dashboard.view',
                'products.view',
                'products.create',
                'products.edit',
                'categories.manage',
                'suppliers.manage',
                'stock.view',
                'stock.receive',
                'stock.adjust',
                'reports.inventory',
            ],

            'guest' => [
                'dashboard.view',
                'products.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($rolePermissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }


}
