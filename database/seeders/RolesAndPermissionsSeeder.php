<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

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

            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.print',

            // Payments and accounts
            'payments.view',
            'payments.edit',
            'payments.delete',
            'payments.record',
            'payments.refund',

            'accounts.view',

            // Reports
            'reports.sales',
            'reports.financial',
            'reports.inventory',

            // System
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles and Permissions
        |--------------------------------------------------------------------------
        */

        $rolePermissions = [

            /*
            |--------------------------------------------------------------------------
            | ADMIN
            |--------------------------------------------------------------------------
            */

            'admin' => $permissions,

            /*
            |--------------------------------------------------------------------------
            | CASHIER
            |--------------------------------------------------------------------------
            */

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
                'invoices.create',
                'invoices.print',

                'payments.view',
                'payments.record',
            ],

            /*
            |--------------------------------------------------------------------------
            | ACCOUNTANT
            |--------------------------------------------------------------------------
            */

            'accountant' => [
                'dashboard.view',

                'customers.view',
                'products.view',

                'stock.view',

                'sales.view',

                // Invoice management
                'invoices.view',
                'invoices.create',
                'invoices.edit',
                'invoices.print',

                // Payment management
                'payments.view',
                'payments.record',
                'payments.edit',

                // Accounts
                'accounts.view',

                // Financial reports
                'reports.sales',
                'reports.financial',
                'reports.inventory',
            ],

            /*
            |--------------------------------------------------------------------------
            | INVENTORY STAFF
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | GUEST
            |--------------------------------------------------------------------------
            */

            'guest' => [],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Roles and Assign Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {

            $role = Role::findOrCreate($roleName, $guard);

            $role->syncPermissions($rolePermissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
