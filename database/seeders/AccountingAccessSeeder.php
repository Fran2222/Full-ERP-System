<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccountingAccessSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'accounting.view' => 'View Accounting Module',
            'accounting.dashboard.view' => 'View Accounting Dashboard',
            'accounting.accounts.view' => 'View Chart of Accounts',
            'accounting.accounts.create' => 'Create Chart of Accounts',
            'accounting.accounts.edit' => 'Edit Chart of Accounts',
            'accounting.accounts.delete' => 'Delete Chart of Accounts',

            'accounting.journal-entries.view' => 'View Journal Entries',
            'accounting.journal-entries.create' => 'Create Journal Entries',
            'accounting.journal-entries.edit' => 'Edit Journal Entries',
            'accounting.journal-entries.delete' => 'Delete Journal Entries',
            'accounting.journal-entries.post' => 'Post Journal Entries',
            'accounting.journal-entries.void' => 'Void Journal Entries',

            'accounting.general-ledger.view' => 'View General Ledger',

            'accounting.bank-accounts.view' => 'View Cash / Bank Accounts',
            'accounting.bank-accounts.create' => 'Create Cash / Bank Accounts',
            'accounting.bank-accounts.edit' => 'Edit Cash / Bank Accounts',
            'accounting.bank-accounts.delete' => 'Delete Cash / Bank Accounts',

            'accounting.collections.view' => 'View Collections',
            'accounting.collections.create' => 'Create Collections',
            'accounting.collections.void' => 'Void Collections',

            'accounting.expenses.view' => 'View Expenses',
            'accounting.expenses.create' => 'Create Expenses',
            'accounting.expenses.void' => 'Void Expenses',

            'accounting.reports.view' => 'View Accounting Reports',
            'accounting.reports.trial-balance.view' => 'View Trial Balance Report',
            'accounting.reports.income-statement.view' => 'View Income Statement Report',
            'accounting.reports.balance-sheet.view' => 'View Balance Sheet Report',
        ];

        foreach ($permissions as $name => $title) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['title' => $title]
            );
        }

        foreach (['super-admin', 'Super Admin', 'admin', 'Admin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $role->givePermissionTo(array_keys($permissions));
            }
        }

        $defaultAccounts = [
            ['code' => '1000', 'name' => 'Cash on Hand', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1010', 'name' => 'Cash in Bank', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1200', 'name' => 'Inventory Asset', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2100', 'name' => 'Goods Received Not Invoiced', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '3000', 'name' => 'Owner Equity', 'type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'cost_of_goods_sold', 'normal_balance' => 'debit'],
            ['code' => '6000', 'name' => 'Operating Expense', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($defaultAccounts as $account) {
            AccountingAccount::firstOrCreate(
                ['code' => $account['code']],
                $account + ['is_active' => true]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
