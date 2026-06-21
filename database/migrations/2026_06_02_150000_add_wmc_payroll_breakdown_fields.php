<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWmcPayrollBreakdownFields extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $employeeColumns = [
                'payroll_sss',
                'payroll_pagibig',
                'payroll_philhealth',
                'payroll_cash_advance',
                'payroll_account_receivables',
                'payroll_stl_mpl',
                'payroll_charitable_contribution',
                'payroll_savings_share',
                'payroll_rice_loan',
                'payroll_loan_payment',
                'payroll_lot_payment',
                'payroll_birthday_savings',
                'payroll_tax_withheld',
                'payroll_allowances',
                'payroll_other_adjustment',
            ];

            foreach ($employeeColumns as $column) {
                if (! Schema::hasColumn('employee_profiles', $column)) {
                    $table->decimal($column, 12, 2)->default(0);
                }
            }
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $itemColumns = [
                'holiday_pay',
                'other_adjustment',
                'grand_total',
                'cash_advance',
                'account_receivables',
                'stl_mpl',
                'charitable_contribution',
                'savings_share',
                'rice_loan',
                'loan_payment',
                'lot_payment',
                'birthday_savings',
                'tax_withheld',
                'allowances',
                'thirteenth_month_pay',
            ];

            foreach ($itemColumns as $column) {
                if (! Schema::hasColumn('payroll_items', $column)) {
                    $table->decimal($column, 12, 2)->default(0);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            foreach ([
                'holiday_pay',
                'other_adjustment',
                'grand_total',
                'cash_advance',
                'account_receivables',
                'stl_mpl',
                'charitable_contribution',
                'savings_share',
                'rice_loan',
                'loan_payment',
                'lot_payment',
                'birthday_savings',
                'tax_withheld',
                'allowances',
                'thirteenth_month_pay',
            ] as $column) {
                if (Schema::hasColumn('payroll_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('employee_profiles', function (Blueprint $table) {
            foreach ([
                'payroll_sss',
                'payroll_pagibig',
                'payroll_philhealth',
                'payroll_cash_advance',
                'payroll_account_receivables',
                'payroll_stl_mpl',
                'payroll_charitable_contribution',
                'payroll_savings_share',
                'payroll_rice_loan',
                'payroll_loan_payment',
                'payroll_lot_payment',
                'payroll_birthday_savings',
                'payroll_tax_withheld',
                'payroll_allowances',
                'payroll_other_adjustment',
            ] as $column) {
                if (Schema::hasColumn('employee_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
