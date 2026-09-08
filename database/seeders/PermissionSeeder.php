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

            // Reports
            ['company_id' => 1, 'module' => 'Reports', 'name' => 'cnf.report.job-expense-summary', 'sorting_order' => 140],

            // ── NAS Freights — company_id = 2 ─────────────────────────────

            // RFQ
            ['company_id' => 2, 'module' => 'RFQ', 'name' => 'freight.rfq.list', 'sorting_order' => 10],
            ['company_id' => 2, 'module' => 'RFQ', 'name' => 'freight.rfq.view', 'sorting_order' => 11],
            ['company_id' => 2, 'module' => 'RFQ', 'name' => 'freight.rfq.create', 'sorting_order' => 12],
            ['company_id' => 2, 'module' => 'RFQ', 'name' => 'freight.rfq.edit', 'sorting_order' => 13],
            ['company_id' => 2, 'module' => 'RFQ', 'name' => 'freight.rfq.delete', 'sorting_order' => 14],
            ['company_id' => 2, 'module' => 'RFQ', 'name' => 'freight.rfq.update-status', 'sorting_order' => 15],
            ['company_id' => 2, 'module' => 'RFQ', 'name' => 'freight.rfq.convert', 'sorting_order' => 16],

            // Freight Import Bookings
            ['company_id' => 2, 'module' => 'Freight Import Bookings', 'name' => 'freight.import-booking.list', 'sorting_order' => 20],
            ['company_id' => 2, 'module' => 'Freight Import Bookings', 'name' => 'freight.import-booking.view', 'sorting_order' => 21],
            ['company_id' => 2, 'module' => 'Freight Import Bookings', 'name' => 'freight.import-booking.create', 'sorting_order' => 22],
            ['company_id' => 2, 'module' => 'Freight Import Bookings', 'name' => 'freight.import-booking.edit', 'sorting_order' => 23],
            ['company_id' => 2, 'module' => 'Freight Import Bookings', 'name' => 'freight.import-booking.delete', 'sorting_order' => 24],

            // Freight Export Bookings
            ['company_id' => 2, 'module' => 'Freight Export Bookings', 'name' => 'freight.export-booking.list', 'sorting_order' => 30],
            ['company_id' => 2, 'module' => 'Freight Export Bookings', 'name' => 'freight.export-booking.view', 'sorting_order' => 31],
            ['company_id' => 2, 'module' => 'Freight Export Bookings', 'name' => 'freight.export-booking.create', 'sorting_order' => 32],
            ['company_id' => 2, 'module' => 'Freight Export Bookings', 'name' => 'freight.export-booking.edit', 'sorting_order' => 33],
            ['company_id' => 2, 'module' => 'Freight Export Bookings', 'name' => 'freight.export-booking.delete', 'sorting_order' => 34],

            // Transport Bookings
            ['company_id' => 2, 'module' => 'Transport Bookings', 'name' => 'freight.booking.list', 'sorting_order' => 40],
            ['company_id' => 2, 'module' => 'Transport Bookings', 'name' => 'freight.booking.create', 'sorting_order' => 41],
            ['company_id' => 2, 'module' => 'Transport Bookings', 'name' => 'freight.booking.edit', 'sorting_order' => 42],
            ['company_id' => 2, 'module' => 'Transport Bookings', 'name' => 'freight.booking.delete', 'sorting_order' => 43],
            ['company_id' => 2, 'module' => 'Transport Bookings', 'name' => 'freight.booking.confirm', 'sorting_order' => 44],
            ['company_id' => 2, 'module' => 'Transport Bookings', 'name' => 'freight.booking.reject', 'sorting_order' => 45],

            // Customer Bills
            ['company_id' => 2, 'module' => 'Customer Bills', 'name' => 'freight.customer-bill.list', 'sorting_order' => 50],
            ['company_id' => 2, 'module' => 'Customer Bills', 'name' => 'freight.customer-bill.view', 'sorting_order' => 51],
            ['company_id' => 2, 'module' => 'Customer Bills', 'name' => 'freight.customer-bill.create', 'sorting_order' => 52],
            ['company_id' => 2, 'module' => 'Customer Bills', 'name' => 'freight.customer-bill.edit', 'sorting_order' => 53],
            ['company_id' => 2, 'module' => 'Customer Bills', 'name' => 'freight.customer-bill.delete', 'sorting_order' => 54],
            ['company_id' => 2, 'module' => 'Customer Bills', 'name' => 'freight.customer-bill.print', 'sorting_order' => 55],
            ['company_id' => 2, 'module' => 'Customer Bills', 'name' => 'freight.customer-bill.confirm', 'sorting_order' => 56],

            // Due List
            ['company_id' => 2, 'module' => 'Due List', 'name' => 'freight.due-list.view', 'sorting_order' => 60],

            // Supplier Bills
            ['company_id' => 2, 'module' => 'Supplier Bills', 'name' => 'freight.supplier-bill.list', 'sorting_order' => 70],
            ['company_id' => 2, 'module' => 'Supplier Bills', 'name' => 'freight.supplier-bill.view', 'sorting_order' => 71],
            ['company_id' => 2, 'module' => 'Supplier Bills', 'name' => 'freight.supplier-bill.create', 'sorting_order' => 72],
            ['company_id' => 2, 'module' => 'Supplier Bills', 'name' => 'freight.supplier-bill.edit', 'sorting_order' => 73],
            ['company_id' => 2, 'module' => 'Supplier Bills', 'name' => 'freight.supplier-bill.delete', 'sorting_order' => 74],
            ['company_id' => 2, 'module' => 'Supplier Bills', 'name' => 'freight.supplier-bill.print', 'sorting_order' => 75],
            ['company_id' => 2, 'module' => 'Supplier Bills', 'name' => 'freight.supplier-bill.confirm', 'sorting_order' => 76],

            // Money Receipts
            ['company_id' => 2, 'module' => 'Money Receipts', 'name' => 'freight.money-receipt.list', 'sorting_order' => 80],
            ['company_id' => 2, 'module' => 'Money Receipts', 'name' => 'freight.money-receipt.view', 'sorting_order' => 81],
            ['company_id' => 2, 'module' => 'Money Receipts', 'name' => 'freight.money-receipt.create', 'sorting_order' => 82],
            ['company_id' => 2, 'module' => 'Money Receipts', 'name' => 'freight.money-receipt.print', 'sorting_order' => 83],

            // Supplier Payments
            ['company_id' => 2, 'module' => 'Supplier Payments', 'name' => 'freight.supplier-payment.list', 'sorting_order' => 90],
            ['company_id' => 2, 'module' => 'Supplier Payments', 'name' => 'freight.supplier-payment.view', 'sorting_order' => 91],
            ['company_id' => 2, 'module' => 'Supplier Payments', 'name' => 'freight.supplier-payment.create', 'sorting_order' => 92],

            // Vehicles
            ['company_id' => 2, 'module' => 'Vehicles', 'name' => 'freight.vehicle.list', 'sorting_order' => 100],
            ['company_id' => 2, 'module' => 'Vehicles', 'name' => 'freight.vehicle.view', 'sorting_order' => 101],
            ['company_id' => 2, 'module' => 'Vehicles', 'name' => 'freight.vehicle.create', 'sorting_order' => 102],
            ['company_id' => 2, 'module' => 'Vehicles', 'name' => 'freight.vehicle.edit', 'sorting_order' => 103],
            ['company_id' => 2, 'module' => 'Vehicles', 'name' => 'freight.vehicle.delete', 'sorting_order' => 104],

            // Suppliers (Stakeholders)
            ['company_id' => 2, 'module' => 'Suppliers', 'name' => 'freight.supplier.list', 'sorting_order' => 110],
            ['company_id' => 2, 'module' => 'Suppliers', 'name' => 'freight.supplier.create', 'sorting_order' => 111],
            ['company_id' => 2, 'module' => 'Suppliers', 'name' => 'freight.supplier.edit', 'sorting_order' => 112],
            ['company_id' => 2, 'module' => 'Suppliers', 'name' => 'freight.supplier.delete', 'sorting_order' => 113],

            // Customers (Stakeholders)
            ['company_id' => 2, 'module' => 'Customers', 'name' => 'freight.customer.list', 'sorting_order' => 120],
            ['company_id' => 2, 'module' => 'Customers', 'name' => 'freight.customer.view', 'sorting_order' => 121],
            ['company_id' => 2, 'module' => 'Customers', 'name' => 'freight.customer.create', 'sorting_order' => 122],
            ['company_id' => 2, 'module' => 'Customers', 'name' => 'freight.customer.edit', 'sorting_order' => 123],
            ['company_id' => 2, 'module' => 'Customers', 'name' => 'freight.customer.delete', 'sorting_order' => 124],

            // Reports
            ['company_id' => 2, 'module' => 'Reports', 'name' => 'freight.report.booking', 'sorting_order' => 130],
            ['company_id' => 2, 'module' => 'Reports', 'name' => 'freight.report.party-bill-summary', 'sorting_order' => 131],
            ['company_id' => 2, 'module' => 'Reports', 'name' => 'freight.report.bill-details', 'sorting_order' => 132],

            // Settings: Branches
            ['company_id' => 2, 'module' => 'Freight Branches', 'name' => 'freight.branch.list', 'sorting_order' => 150],
            ['company_id' => 2, 'module' => 'Freight Branches', 'name' => 'freight.branch.create', 'sorting_order' => 151],
            ['company_id' => 2, 'module' => 'Freight Branches', 'name' => 'freight.branch.edit', 'sorting_order' => 152],
            ['company_id' => 2, 'module' => 'Freight Branches', 'name' => 'freight.branch.delete', 'sorting_order' => 153],

            // Settings: Container Types
            ['company_id' => 2, 'module' => 'Container Types', 'name' => 'freight.container-type.list', 'sorting_order' => 160],
            ['company_id' => 2, 'module' => 'Container Types', 'name' => 'freight.container-type.create', 'sorting_order' => 161],
            ['company_id' => 2, 'module' => 'Container Types', 'name' => 'freight.container-type.edit', 'sorting_order' => 162],
            ['company_id' => 2, 'module' => 'Container Types', 'name' => 'freight.container-type.delete', 'sorting_order' => 163],

            // Settings: Package Types
            ['company_id' => 2, 'module' => 'Package Types', 'name' => 'freight.package-type.list', 'sorting_order' => 170],
            ['company_id' => 2, 'module' => 'Package Types', 'name' => 'freight.package-type.create', 'sorting_order' => 171],
            ['company_id' => 2, 'module' => 'Package Types', 'name' => 'freight.package-type.edit', 'sorting_order' => 172],
            ['company_id' => 2, 'module' => 'Package Types', 'name' => 'freight.package-type.delete', 'sorting_order' => 173],

            // Settings: Overseas Agents
            ['company_id' => 2, 'module' => 'Overseas Agents', 'name' => 'freight.overseas-agent.list', 'sorting_order' => 180],
            ['company_id' => 2, 'module' => 'Overseas Agents', 'name' => 'freight.overseas-agent.create', 'sorting_order' => 181],
            ['company_id' => 2, 'module' => 'Overseas Agents', 'name' => 'freight.overseas-agent.edit', 'sorting_order' => 182],
            ['company_id' => 2, 'module' => 'Overseas Agents', 'name' => 'freight.overseas-agent.delete', 'sorting_order' => 183],

            // Settings: Shipping Carriers
            ['company_id' => 2, 'module' => 'Shipping Carriers', 'name' => 'freight.shipping-carrier.list', 'sorting_order' => 190],
            ['company_id' => 2, 'module' => 'Shipping Carriers', 'name' => 'freight.shipping-carrier.create', 'sorting_order' => 191],
            ['company_id' => 2, 'module' => 'Shipping Carriers', 'name' => 'freight.shipping-carrier.edit', 'sorting_order' => 192],
            ['company_id' => 2, 'module' => 'Shipping Carriers', 'name' => 'freight.shipping-carrier.delete', 'sorting_order' => 193],

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
