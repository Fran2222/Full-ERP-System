<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_receipts', 'voided_at')) {
                $table->timestamp('voided_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('sales_receipts', 'voided_by')) {
                $table->unsignedBigInteger('voided_by')->nullable()->after('voided_at');
            }

            if (! Schema::hasColumn('sales_receipts', 'void_reason')) {
                $table->text('void_reason')->nullable()->after('voided_by');
            }
        });

        $payload = [
            'name' => 'sales.receipts.void',
            'guard_name' => 'web',
        ];

        $values = [];
        if (Schema::hasColumn('permissions', 'title')) {
            $values['title'] = 'Sales Receipts Void';
        }

        $permission = Permission::firstOrCreate($payload, $values);

        $adminRoleNames = [
            'Super Admin',
            'Super Administrator',
            'Admin',
            'super-admin',
            'super admin',
            'superadmin',
            'admin',
        ];

        Role::whereIn('name', $adminRoleNames)->get()->each(function ($role) use ($permission) {
            if (! $role->hasPermissionTo($permission->name)) {
                $role->givePermissionTo($permission);
            }
        });

        // Existing Sales Manager/Admin module-assigned users should get the new void permission immediately.
        if (Schema::hasTable('user_module_assignments') && Schema::hasTable('model_has_permissions')) {
            $userIds = DB::table('user_module_assignments')
                ->where('module', 'sales')
                ->whereIn('access_level', ['manager', 'admin'])
                ->pluck('user_id')
                ->unique()
                ->values();

            foreach ($userIds as $userId) {
                DB::table('model_has_permissions')->updateOrInsert([
                    'permission_id' => $permission->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $userId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep audit columns/data safe. Do not auto-drop void information.
    }
};
