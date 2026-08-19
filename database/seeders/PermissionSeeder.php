<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // ── System (company_id = null) ─────────────────────────────────

            // Admin Users
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.list',   'sorting_order' => 10],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.view',   'sorting_order' => 11],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.create', 'sorting_order' => 12],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.edit',   'sorting_order' => 13],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.delete', 'sorting_order' => 14],

            // Roles
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.list',   'sorting_order' => 20],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.view',   'sorting_order' => 21],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.create', 'sorting_order' => 22],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.edit',   'sorting_order' => 23],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.delete', 'sorting_order' => 24],

            // Companies
            ['company_id' => null, 'module' => 'Companies', 'name' => 'admin.companies.list', 'sorting_order' => 30],
            ['company_id' => null, 'module' => 'Companies', 'name' => 'admin.companies.edit', 'sorting_order' => 31],

            // Employees
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.list',   'sorting_order' => 40],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.view',   'sorting_order' => 41],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.create', 'sorting_order' => 42],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.edit',   'sorting_order' => 43],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.delete', 'sorting_order' => 44],

            // Designations
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.list',   'sorting_order' => 50],
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.create', 'sorting_order' => 51],
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.edit',   'sorting_order' => 52],
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.delete', 'sorting_order' => 53],

            // ── Chevron Lines (C&F) — company_id = 1 ──────────────────────

            // Jobs
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'jobs.list',   'sorting_order' => 10],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'jobs.view',   'sorting_order' => 11],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'jobs.create', 'sorting_order' => 12],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'jobs.edit',   'sorting_order' => 13],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'jobs.delete', 'sorting_order' => 14],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'jobs.print',  'sorting_order' => 15],

            // Expenses
            ['company_id' => 1, 'module' => 'Expenses', 'name' => 'expenses.list',   'sorting_order' => 20],
            ['company_id' => 1, 'module' => 'Expenses', 'name' => 'expenses.view',   'sorting_order' => 21],
            ['company_id' => 1, 'module' => 'Expenses', 'name' => 'expenses.create', 'sorting_order' => 22],
            ['company_id' => 1, 'module' => 'Expenses', 'name' => 'expenses.edit',   'sorting_order' => 23],
            ['company_id' => 1, 'module' => 'Expenses', 'name' => 'expenses.delete', 'sorting_order' => 24],

            // ── NAS Freights — company_id = 2 ─────────────────────────────

            // Bookings
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.list',    'sorting_order' => 10],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.view',    'sorting_order' => 11],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.create',  'sorting_order' => 12],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.edit',    'sorting_order' => 13],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.delete',  'sorting_order' => 14],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.print',   'sorting_order' => 15],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.confirm', 'sorting_order' => 16],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.reject',  'sorting_order' => 17],

            // ── NAS Trading — company_id = 3 ──────────────────────────────

            // LCs
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.list',          'sorting_order' => 10],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.view',          'sorting_order' => 11],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.create',        'sorting_order' => 12],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.edit',          'sorting_order' => 13],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.delete',        'sorting_order' => 14],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.print',         'sorting_order' => 15],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.generate_bill', 'sorting_order' => 16],

        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'company_id' => $perm['company_id']],
                ['guard_name' => 'web', 'module' => $perm['module'], 'sorting_order' => $perm['sorting_order']]
            );
        }
    }
}
