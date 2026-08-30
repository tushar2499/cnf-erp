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
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.list', 'sorting_order' => 10],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.view', 'sorting_order' => 11],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.create', 'sorting_order' => 12],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.edit', 'sorting_order' => 13],
            ['company_id' => null, 'module' => 'Admin Users', 'name' => 'admin.users.delete', 'sorting_order' => 14],

            // Roles
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.list', 'sorting_order' => 20],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.view', 'sorting_order' => 21],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.create', 'sorting_order' => 22],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.edit', 'sorting_order' => 23],
            ['company_id' => null, 'module' => 'Roles', 'name' => 'admin.roles.delete', 'sorting_order' => 24],

            // Companies
            ['company_id' => null, 'module' => 'Companies', 'name' => 'admin.companies.list', 'sorting_order' => 30],
            ['company_id' => null, 'module' => 'Companies', 'name' => 'admin.companies.edit', 'sorting_order' => 31],

            // Employees
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.list', 'sorting_order' => 40],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.view', 'sorting_order' => 41],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.create', 'sorting_order' => 42],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.edit', 'sorting_order' => 43],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.delete', 'sorting_order' => 44],
            ['company_id' => null, 'module' => 'Employees', 'name' => 'admin.employees.branch-access', 'sorting_order' => 45],

            // Designations
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.list', 'sorting_order' => 50],
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.create', 'sorting_order' => 51],
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.edit', 'sorting_order' => 52],
            ['company_id' => null, 'module' => 'Designations', 'name' => 'admin.designations.delete', 'sorting_order' => 53],

// ── Chevron Lines (C&F) — company_id = 1 ──────────────────────

            // Jobs
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'cnf.job.list', 'sorting_order' => 10],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'cnf.job.view', 'sorting_order' => 11],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'cnf.job.create', 'sorting_order' => 12],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'cnf.job.edit', 'sorting_order' => 13],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'cnf.job.delete', 'sorting_order' => 14],
            ['company_id' => 1, 'module' => 'Jobs', 'name' => 'cnf.job.print', 'sorting_order' => 15],

            // Job Expenses
            ['company_id' => 1, 'module' => 'Job Expenses', 'name' => 'cnf.job-expense.list', 'sorting_order' => 20],
            ['company_id' => 1, 'module' => 'Job Expenses', 'name' => 'cnf.job-expense.create', 'sorting_order' => 21],
            ['company_id' => 1, 'module' => 'Job Expenses', 'name' => 'cnf.job-expense.edit', 'sorting_order' => 22],
            ['company_id' => 1, 'module' => 'Job Expenses', 'name' => 'cnf.job-expense.delete', 'sorting_order' => 23],

            // Bills
            ['company_id' => 1, 'module' => 'Bills', 'name' => 'cnf.bill.list', 'sorting_order' => 30],
            ['company_id' => 1, 'module' => 'Bills', 'name' => 'cnf.bill.create', 'sorting_order' => 31],
            ['company_id' => 1, 'module' => 'Bills', 'name' => 'cnf.bill.edit', 'sorting_order' => 32],
            ['company_id' => 1, 'module' => 'Bills', 'name' => 'cnf.bill.delete', 'sorting_order' => 33],
            ['company_id' => 1, 'module' => 'Bills', 'name' => 'cnf.bill.print', 'sorting_order' => 34],

            // Money Receipts
            ['company_id' => 1, 'module' => 'Money Receipts', 'name' => 'cnf.money-receipt.list', 'sorting_order' => 40],
            ['company_id' => 1, 'module' => 'Money Receipts', 'name' => 'cnf.money-receipt.create', 'sorting_order' => 41],
            ['company_id' => 1, 'module' => 'Money Receipts', 'name' => 'cnf.money-receipt.edit', 'sorting_order' => 42],
            ['company_id' => 1, 'module' => 'Money Receipts', 'name' => 'cnf.money-receipt.delete', 'sorting_order' => 43],

            // Customers
            ['company_id' => 1, 'module' => 'Customers', 'name' => 'cnf.customer.list', 'sorting_order' => 50],
            ['company_id' => 1, 'module' => 'Customers', 'name' => 'cnf.customer.create', 'sorting_order' => 51],
            ['company_id' => 1, 'module' => 'Customers', 'name' => 'cnf.customer.edit', 'sorting_order' => 52],
            ['company_id' => 1, 'module' => 'Customers', 'name' => 'cnf.customer.delete', 'sorting_order' => 53],

            // Settings: Services
            ['company_id' => 1, 'module' => 'Services', 'name' => 'cnf.service.list', 'sorting_order' => 60],
            ['company_id' => 1, 'module' => 'Services', 'name' => 'cnf.service.create', 'sorting_order' => 61],
            ['company_id' => 1, 'module' => 'Services', 'name' => 'cnf.service.edit', 'sorting_order' => 62],
            ['company_id' => 1, 'module' => 'Services', 'name' => 'cnf.service.delete', 'sorting_order' => 63],

            // Settings: Job Types
            ['company_id' => 1, 'module' => 'Job Types', 'name' => 'cnf.job-type.list', 'sorting_order' => 70],
            ['company_id' => 1, 'module' => 'Job Types', 'name' => 'cnf.job-type.create', 'sorting_order' => 71],
            ['company_id' => 1, 'module' => 'Job Types', 'name' => 'cnf.job-type.edit', 'sorting_order' => 72],
            ['company_id' => 1, 'module' => 'Job Types', 'name' => 'cnf.job-type.delete', 'sorting_order' => 73],

            // Settings: Ports
            ['company_id' => 1, 'module' => 'Ports', 'name' => 'cnf.port.list', 'sorting_order' => 80],
            ['company_id' => 1, 'module' => 'Ports', 'name' => 'cnf.port.create', 'sorting_order' => 81],
            ['company_id' => 1, 'module' => 'Ports', 'name' => 'cnf.port.edit', 'sorting_order' => 82],
            ['company_id' => 1, 'module' => 'Ports', 'name' => 'cnf.port.delete', 'sorting_order' => 83],

            // Settings: Items
            ['company_id' => 1, 'module' => 'Items', 'name' => 'cnf.item.list', 'sorting_order' => 90],
            ['company_id' => 1, 'module' => 'Items', 'name' => 'cnf.item.create', 'sorting_order' => 91],
            ['company_id' => 1, 'module' => 'Items', 'name' => 'cnf.item.edit', 'sorting_order' => 92],
            ['company_id' => 1, 'module' => 'Items', 'name' => 'cnf.item.delete', 'sorting_order' => 93],

            // Settings: Branches
            ['company_id' => 1, 'module' => 'Branches', 'name' => 'cnf.branch.list', 'sorting_order' => 100],
            ['company_id' => 1, 'module' => 'Branches', 'name' => 'cnf.branch.create', 'sorting_order' => 101],
            ['company_id' => 1, 'module' => 'Branches', 'name' => 'cnf.branch.edit', 'sorting_order' => 102],
            ['company_id' => 1, 'module' => 'Branches', 'name' => 'cnf.branch.delete', 'sorting_order' => 103],

            // Settings: Expense Heads
            ['company_id' => 1, 'module' => 'Expense Heads', 'name' => 'cnf.expense-head.list', 'sorting_order' => 110],
            ['company_id' => 1, 'module' => 'Expense Heads', 'name' => 'cnf.expense-head.create', 'sorting_order' => 111],
            ['company_id' => 1, 'module' => 'Expense Heads', 'name' => 'cnf.expense-head.edit', 'sorting_order' => 112],
            ['company_id' => 1, 'module' => 'Expense Heads', 'name' => 'cnf.expense-head.delete', 'sorting_order' => 113],

            // Settings: Accounts
            ['company_id' => 1, 'module' => 'Accounts', 'name' => 'cnf.account.list', 'sorting_order' => 120],
            ['company_id' => 1, 'module' => 'Accounts', 'name' => 'cnf.account.create', 'sorting_order' => 121],
            ['company_id' => 1, 'module' => 'Accounts', 'name' => 'cnf.account.edit', 'sorting_order' => 122],
            ['company_id' => 1, 'module' => 'Accounts', 'name' => 'cnf.account.delete', 'sorting_order' => 123],

            // Settings: Expense Categories
            ['company_id' => 1, 'module' => 'Expense Categories', 'name' => 'cnf.expense-category.list', 'sorting_order' => 130],
            ['company_id' => 1, 'module' => 'Expense Categories', 'name' => 'cnf.expense-category.create', 'sorting_order' => 131],
            ['company_id' => 1, 'module' => 'Expense Categories', 'name' => 'cnf.expense-category.edit', 'sorting_order' => 132],
            ['company_id' => 1, 'module' => 'Expense Categories', 'name' => 'cnf.expense-category.delete', 'sorting_order' => 133],

// ── NAS Freights — company_id = 2 ─────────────────────────────

            // Bookings
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.list', 'sorting_order' => 10],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.view', 'sorting_order' => 11],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.create', 'sorting_order' => 12],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.edit', 'sorting_order' => 13],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.delete', 'sorting_order' => 14],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.print', 'sorting_order' => 15],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.confirm', 'sorting_order' => 16],
            ['company_id' => 2, 'module' => 'Bookings', 'name' => 'bookings.reject', 'sorting_order' => 17],

// ── NAS Trading — company_id = 3 ──────────────────────────────

            // LCs
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.list', 'sorting_order' => 10],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.view', 'sorting_order' => 11],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.create', 'sorting_order' => 12],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.edit', 'sorting_order' => 13],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.delete', 'sorting_order' => 14],
            ['company_id' => 3, 'module' => 'LCs', 'name' => 'lcs.print', 'sorting_order' => 15],
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
