<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Warehouse\WarehouseLocation;
use App\Models\Warehouse\WarehouseTransfer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemNotificationService
{
    public static function notifyUser(
        int $userId,
        string $module,
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $createdBy = null
    ): ?SystemNotification {
        if (! Schema::hasTable('system_notifications')) {
            return null;
        }

        return SystemNotification::firstOrCreate(
            [
                'user_id' => $userId,
                'type' => $type,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
            ],
            [
                'module' => $module,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'is_read' => false,
                'created_by' => $createdBy,
            ]
        );
    }

    public static function notifyUsers(
        $userIds,
        string $module,
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $createdBy = null
    ): void {
        collect($userIds)
            ->filter()
            ->unique()
            ->values()
            ->each(function ($userId) use ($module, $type, $title, $message, $actionUrl, $relatedType, $relatedId, $createdBy) {
                self::notifyUser((int) $userId, $module, $type, $title, $message, $actionUrl, $relatedType, $relatedId, $createdBy);
            });
    }

        public static function notifyWarehouseTransferDispatched(WarehouseTransfer $transfer, ?int $createdBy = null): void
    {
        if (! Schema::hasTable('system_notifications')) {
            return;
        }

        $transfer->loadMissing(['fromBranch', 'fromLocation', 'toBranch', 'toLocation']);

        $recipientIds = self::warehouseTransferReceiverUserIds($transfer)
            ->reject(fn ($userId) => $createdBy && (int) $userId === (int) $createdBy)
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $fromName = optional($transfer->fromLocation)->location_name
            ?? optional($transfer->fromLocation)->name
            ?? 'source';

        $toName = optional($transfer->toLocation)->location_name
            ?? optional($transfer->toLocation)->name
            ?? optional($transfer->toBranch)->name
            ?? 'destination';

        self::notifyUsers(
            $recipientIds,
            'warehouse',
            'warehouse_transfer_to_receive',
            'Warehouse Transfer To Receive',
            "{$transfer->transfer_no} was dispatched from {$fromName} to {$toName}.",
            url('/warehouse/transfer/' . $transfer->id),
            'warehouse_transfer',
            (int) $transfer->id,
            $createdBy
        );
    }

        public static function warehouseTransferReceiverUserIds(WarehouseTransfer $transfer): Collection
    {
        $transfer->loadMissing(['toBranch', 'toLocation']);

        $toLocation = $transfer->toLocation ?: WarehouseLocation::find($transfer->to_location_id);
        $isWarehouseDestination = self::isWarehouseLocation($toLocation);

        $destinationBranchId = null;
        if ($toLocation && ! empty($toLocation->branch_id)) {
            $destinationBranchId = (int) $toLocation->branch_id;
        } elseif (! empty($transfer->to_branch_id)) {
            $destinationBranchId = (int) $transfer->to_branch_id;
        }

        return User::query()
            ->get()
            ->filter(function ($user) use ($isWarehouseDestination, $destinationBranchId) {
                if (! self::isActiveUser($user)) {
                    return false;
                }

                // Branch destination:
                // only the destination branch users with warehouse/inventory module access are involved.
                // This prevents source users/dispatchers from receiving "to receive" notifications.
                if ($destinationBranchId) {
                    return (int) ($user->branch_id ?? 0) === (int) $destinationBranchId
                        && self::hasAnyModuleAccess($user, ['warehouse', 'inventory']);
                }

                // Central/warehouse destination with no branch:
                // warehouse/inventory assigned users or actual Warehouse Department users are involved.
                if ($isWarehouseDestination) {
                    return self::hasAnyModuleAccess($user, ['warehouse', 'inventory'])
                        || self::isWarehouseDepartmentUser($user);
                }

                return false;
            })
            ->pluck('id')
            ->values();
    }

    public static function notifyWarehouseTransferReceived(WarehouseTransfer $transfer, ?int $createdBy = null): void
    {
        if (! Schema::hasTable('system_notifications')) {
            return;
        }

        $transfer->loadMissing(['fromBranch', 'fromLocation', 'toBranch', 'toLocation']);

        $recipientIds = self::warehouseTransferSourceUserIds($transfer)
            ->merge([
                $transfer->created_by ?? null,
                $transfer->dispatched_by ?? null,
            ])
            ->filter()
            ->unique()
            ->reject(fn ($userId) => $createdBy && (int) $userId === (int) $createdBy)
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $fromName = optional($transfer->fromLocation)->location_name
            ?? optional($transfer->fromLocation)->name
            ?? optional($transfer->fromBranch)->name
            ?? 'source';

        $toName = optional($transfer->toLocation)->location_name
            ?? optional($transfer->toLocation)->name
            ?? optional($transfer->toBranch)->name
            ?? 'destination';

        self::notifyUsers(
            $recipientIds,
            'warehouse',
            'warehouse_transfer_received',
            'Warehouse Transfer Received',
            "{$transfer->transfer_no} was received at {$toName}. Source: {$fromName}.",
            url('/warehouse/transfer/' . $transfer->id),
            'warehouse_transfer',
            (int) $transfer->id,
            $createdBy
        );
    }

    public static function notifyWarehouseTransferCancelled(WarehouseTransfer $transfer, ?int $createdBy = null): void
    {
        if (! Schema::hasTable('system_notifications')) {
            return;
        }

        $transfer->loadMissing(['fromBranch', 'fromLocation', 'toBranch', 'toLocation']);

        $recipientIds = self::warehouseTransferSourceUserIds($transfer)
            ->merge(self::warehouseTransferReceiverUserIds($transfer))
            ->merge([
                $transfer->created_by ?? null,
                $transfer->dispatched_by ?? null,
                $transfer->received_by ?? null,
            ])
            ->filter()
            ->unique()
            ->reject(fn ($userId) => $createdBy && (int) $userId === (int) $createdBy)
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $fromName = optional($transfer->fromLocation)->location_name
            ?? optional($transfer->fromLocation)->name
            ?? optional($transfer->fromBranch)->name
            ?? 'source';

        $toName = optional($transfer->toLocation)->location_name
            ?? optional($transfer->toLocation)->name
            ?? optional($transfer->toBranch)->name
            ?? 'destination';

        self::notifyUsers(
            $recipientIds,
            'warehouse',
            'warehouse_transfer_cancelled',
            'Warehouse Transfer Cancelled',
            "{$transfer->transfer_no} from {$fromName} to {$toName} was cancelled.",
            url('/warehouse/transfer/' . $transfer->id),
            'warehouse_transfer',
            (int) $transfer->id,
            $createdBy
        );
    }

    public static function warehouseTransferSourceUserIds(WarehouseTransfer $transfer): Collection
    {
        $transfer->loadMissing(['fromBranch', 'fromLocation']);

        $fromLocation = $transfer->fromLocation ?: WarehouseLocation::find($transfer->from_location_id);
        $isWarehouseSource = self::isWarehouseLocation($fromLocation);
        $sourceBranchId = $fromLocation->branch_id ?? $transfer->from_branch_id ?? null;

        return User::query()
            ->get()
            ->filter(function ($user) use ($isWarehouseSource, $sourceBranchId) {
                if (! self::isActiveUser($user)) {
                    return false;
                }

                if (self::isAdminOrBod($user)) {
                    return true;
                }

                if ($isWarehouseSource) {
                    return self::isWarehouseDepartmentUser($user);
                }

                if (! $sourceBranchId || (int) ($user->branch_id ?? 0) !== (int) $sourceBranchId) {
                    return false;
                }

                return self::hasModuleAccess($user, 'warehouse') || self::hasModuleAccess($user, 'inventory');
            })
            ->pluck('id')
            ->values();
    }

    public static function unreadCountForUser(?int $userId): int
    {
        if (! $userId || ! Schema::hasTable('system_notifications')) {
            return 0;
        }

        return SystemNotification::where('user_id', $userId)->unread()->count();
    }

    public static function latestForUser(?int $userId, int $limit = 5): Collection
    {
        if (! $userId || ! Schema::hasTable('system_notifications')) {
            return collect();
        }

        return SystemNotification::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    private static function isActiveUser($user): bool
    {
        $status = strtolower((string) ($user->status ?? 'active'));

        return in_array($status, ['active', '1', 'true', 'enabled'], true);
    }

    private static function isAdminOrBod($user): bool
    {
        $roleNames = [
            'Super Admin',
            'Super Administrator',
            'super admin',
            'super-admin',
            'superadmin',
            'Admin',
            'admin',
            'BOD',
            'bod',
            'Board of Directors',
        ];

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roleNames)) {
            return true;
        }

        $userType = strtolower((string) ($user->user_type ?? ''));

        return in_array($userType, ['admin', 'super-admin', 'superadmin', 'bod'], true);
    }

            private static function hasModuleAccess($user, string $module): bool
    {
        return self::hasAnyModuleAccess($user, [$module]);
    }




                private static function hasAnyModuleAccess($user, array $modules): bool
    {
        $modules = collect($modules)
            ->map(fn ($module) => strtolower(trim((string) $module)))
            ->filter()
            ->values()
            ->all();

        if (! $user || empty($modules)) {
            return false;
        }

        $primaryModule = strtolower(trim((string) ($user->primary_module ?? '')));
        if ($primaryModule && in_array($primaryModule, $modules, true)) {
            return true;
        }

        // Current deployed module assignment table.
        // This is the main source for employees/users with assigned module and access level.
        if (Schema::hasTable('user_module_assignments')) {
            $assignmentQuery = DB::table('user_module_assignments')
                ->where('user_id', $user->id)
                ->whereIn(DB::raw('LOWER(TRIM(module))'), $modules);

            // For notification eligibility, these access levels are considered involved.
            // Add/remove here later if your company defines stricter module-level rules.
            if (Schema::hasColumn('user_module_assignments', 'access_level')) {
                $assignmentQuery->whereIn(DB::raw('LOWER(TRIM(access_level))'), [
                    'staff',
                    'manager',
                    'admin',
                    'supervisor',
                    'head',
                    'receiver',
                    'approver',
                    'viewer',
                    'bod',
                    'super-admin',
                    'super admin',
                    'superadministrator',
                    'super administrator',
                ]);
            }

            if ($assignmentQuery->exists()) {
                return true;
            }
        }

        // Backward compatibility for older table name used by some patches/builds.
        if (Schema::hasTable('user_module_accesses')) {
            $accessQuery = DB::table('user_module_accesses')
                ->where('user_id', $user->id)
                ->whereIn(DB::raw('LOWER(TRIM(module))'), $modules);

            if (Schema::hasColumn('user_module_accesses', 'access_level')) {
                $accessQuery->whereIn(DB::raw('LOWER(TRIM(access_level))'), [
                    'staff',
                    'manager',
                    'admin',
                    'supervisor',
                    'head',
                    'receiver',
                    'approver',
                    'viewer',
                    'bod',
                    'super-admin',
                    'super admin',
                    'superadministrator',
                    'super administrator',
                ]);
            }

            if ($accessQuery->exists()) {
                return true;
            }
        }

        return false;
    }

    private static function isWarehouseDepartmentUser($user): bool
    {
        if (! Schema::hasTable('departments') || empty($user->department_id)) {
            return false;
        }

        $departmentName = DB::table('departments')
            ->where('id', $user->department_id)
            ->value('name');

        return str_contains(strtolower((string) $departmentName), 'warehouse');
    }

        private static function isWarehouseLocation($location): bool
    {
        if (! $location) {
            return false;
        }

        $type = strtolower(trim((string) ($location->location_type ?? '')));
        $name = strtolower(trim((string) (($location->location_name ?? '') . ' ' . ($location->name ?? '') . ' ' . ($location->location_code ?? ''))));

        return str_contains($type, 'warehouse')
            || str_contains($type, 'stock room')
            || str_contains($type, 'stockroom')
            || str_contains($name, 'warehouse')
            || str_contains($name, 'main warehouse');
    }


        public static function notifyPurchaseOrderMarkedOrdered($purchaseOrder, ?int $actorId = null): void
    {
        try {
            $poId = (int) ($purchaseOrder->id ?? 0);
            if ($poId <= 0) {
                return;
            }

            $poNo = (string) ($purchaseOrder->po_no ?? ('PO #' . $poId));
            $supplierName = self::wmcPurchasingSupplierName($purchaseOrder->supplier_id ?? null);

            $directUsers = collect([
                $purchaseOrder->created_by ?? null,
                $purchaseOrder->requested_by ?? null,
                $purchaseOrder->prepared_by ?? null,
                $purchaseOrder->approved_by ?? null,
                $purchaseOrder->ordered_by ?? null,
            ])->filter();

            $recipients = collect()
                ->merge(self::wmcActionNotificationUserIds(['purchasing', 'procurement'], ['staff', 'manager', 'admin', 'approver'], $actorId))
                ->merge(self::wmcActionNotificationUserIds(['warehouse', 'inventory'], ['staff', 'manager', 'admin', 'receiver'], $actorId))
                ->merge($directUsers)
                ->filter(fn ($id) => (int) $id > 0 && (! $actorId || (int) $id !== (int) $actorId))
                ->unique()
                ->values();

            self::notifyUsers(
                $recipients,
                'purchasing',
                'purchase_order_ordered',
                'Purchase Order Ready for Receiving',
                trim($poNo . ($supplierName ? ' for ' . $supplierName : '') . ' is now ordered/approved and ready for receiving.'),
                url('/purchasing/purchase-orders/' . $poId),
                'purchase_order',
                $poId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

        public static function notifyPurchaseOrderReceived($purchaseOrder, ?int $receivingId = null, ?string $status = null, ?int $actorId = null): void
    {
        try {
            $poId = (int) ($purchaseOrder->id ?? 0);
            if ($poId <= 0) {
                return;
            }

            $poNo = (string) ($purchaseOrder->po_no ?? ('PO #' . $poId));
            $statusText = $status === 'received' ? 'fully received' : 'partially received';

            $directUsers = collect([
                $purchaseOrder->created_by ?? null,
                $purchaseOrder->approved_by ?? null,
                $purchaseOrder->requested_by ?? null,
                $purchaseOrder->prepared_by ?? null,
                $purchaseOrder->ordered_by ?? null,
            ])->filter();

            $recipients = collect()
                // Purchasing is involved because they need receiving status updates.
                ->merge(self::wmcResolveInvolvedUserIds([
                    'modules' => ['purchasing', 'procurement'],
                    'access_levels' => ['staff', 'manager', 'admin', 'approver'],
                ]))
                ->merge($directUsers)
                ->filter(fn ($id) => (int) $id > 0 && (! $actorId || (int) $id !== (int) $actorId))
                ->unique()
                ->values();

            self::wmcCreateNotifications(
                $recipients,
                'purchasing',
                'purchase_order_received',
                'Purchase Order Received',
                $poNo . ' has been ' . $statusText . '.',
                self::wmcRouteUrl('purchasing.purchase-orders.show', [$poId], '/purchasing/purchase-orders/' . $poId),
                'purchase_order',
                $poId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

        public static function notifyPurchaseBillCreated($purchaseBill, ?int $actorId = null): void
    {
        try {
            $billId = (int) ($purchaseBill->id ?? 0);
            if ($billId <= 0) {
                return;
            }

            $billNo = (string) ($purchaseBill->bill_no ?? ('Bill #' . $billId));
            $poNo = null;

            if (! empty($purchaseBill->purchase_order_id) && \Illuminate\Support\Facades\Schema::hasTable('purchase_orders')) {
                $poNo = \Illuminate\Support\Facades\DB::table('purchase_orders')
                    ->where('id', $purchaseBill->purchase_order_id)
                    ->value('po_no');
            }

            $directUsers = collect([
                $purchaseBill->created_by ?? null,
                $purchaseBill->prepared_by ?? null,
                $purchaseBill->approved_by ?? null,
            ])->filter();

            $recipients = collect()
                // Accounting/Finance users are involved because this is now payable/payment workflow.
                ->merge(self::wmcResolveInvolvedUserIds([
                    'modules' => ['accounting', 'finance'],
                    'access_levels' => ['staff', 'manager', 'admin', 'approver'],
                    'department_keywords' => ['accounting', 'finance'],
                ]))
                ->merge($directUsers)
                ->filter(fn ($id) => (int) $id > 0 && (! $actorId || (int) $id !== (int) $actorId))
                ->unique()
                ->values();

            self::wmcCreateNotifications(
                $recipients,
                'accounting',
                'purchase_bill_created',
                'New Supplier Bill To Pay',
                trim($billNo . ($poNo ? ' from ' . $poNo : '') . ' is posted and ready for Accounting payment.'),
                self::wmcRouteUrl('accounting.pay-bills.create', ['purchase_bill_id' => $billId], '/accounting/pay-bills/create?purchase_bill_id=' . $billId),
                'purchase_bill',
                $billId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function wmcPurchasingSupplierName($supplierId): ?string
    {
        try {
            if (! $supplierId || ! \Illuminate\Support\Facades\Schema::hasTable('warehouse_suppliers')) {
                return null;
            }

            $supplier = \Illuminate\Support\Facades\DB::table('warehouse_suppliers')->where('id', $supplierId)->first();
            return $supplier->supplier_name ?? $supplier->name ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

        private static function wmcNotificationUsersByModules(array $modules, array $accessLevels = []): \Illuminate\Support\Collection
    {
        try {
            $modules = collect($modules)
                ->map(fn ($module) => strtolower(trim((string) $module)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $accessLevels = collect($accessLevels)
                ->map(fn ($level) => strtolower(trim((string) $level)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($modules)) {
                return collect();
            }

            $userIds = collect();

            // 1) Actual deployed source of truth for this system.
            if (\Illuminate\Support\Facades\Schema::hasTable('user_module_assignments')) {
                $query = \Illuminate\Support\Facades\DB::table('user_module_assignments as uma')
                    ->join('users as u', 'u.id', '=', 'uma.user_id')
                    ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.module))'), $modules);

                if (! empty($accessLevels) && \Illuminate\Support\Facades\Schema::hasColumn('user_module_assignments', 'access_level')) {
                    $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.access_level))'), $accessLevels);
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                    $query->where(function ($q) {
                        $q->whereNull('u.status')
                            ->orWhere('u.status', 'active')
                            ->orWhere('u.status', 1)
                            ->orWhere('u.status', true)
                            ->orWhere('u.status', '1');
                    });
                }

                $userIds = $userIds->merge($query->pluck('u.id'));
            }

            // 2) Fallback for users that only have users.primary_module populated.
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'primary_module')) {
                $query = \Illuminate\Support\Facades\DB::table('users as u')
                    ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(u.primary_module))'), $modules);

                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                    $query->where(function ($q) {
                        $q->whereNull('u.status')
                            ->orWhere('u.status', 'active')
                            ->orWhere('u.status', 1)
                            ->orWhere('u.status', true)
                            ->orWhere('u.status', '1');
                    });
                }

                $userIds = $userIds->merge($query->pluck('u.id'));
            }

            // 3) Backward compatibility for older patch/build table.
            if (\Illuminate\Support\Facades\Schema::hasTable('user_module_accesses')) {
                $query = \Illuminate\Support\Facades\DB::table('user_module_accesses as uma')
                    ->join('users as u', 'u.id', '=', 'uma.user_id')
                    ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.module))'), $modules);

                if (! empty($accessLevels) && \Illuminate\Support\Facades\Schema::hasColumn('user_module_accesses', 'access_level')) {
                    $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.access_level))'), $accessLevels);
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                    $query->where(function ($q) {
                        $q->whereNull('u.status')
                            ->orWhere('u.status', 'active')
                            ->orWhere('u.status', 1)
                            ->orWhere('u.status', true)
                            ->orWhere('u.status', '1');
                    });
                }

                $userIds = $userIds->merge($query->pluck('u.id'));
            }

            return $userIds->filter()->unique()->values();
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }

    private static function wmcNotificationUsersByDepartmentKeywords(array $keywords): \Illuminate\Support\Collection
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('departments') || ! \Illuminate\Support\Facades\Schema::hasColumn('users', 'department_id')) {
                return collect();
            }

            $query = \Illuminate\Support\Facades\DB::table('users as u')
                ->join('departments as d', 'd.id', '=', 'u.department_id')
                ->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('d.name', 'ilike', '%' . $keyword . '%');
                        if (\Illuminate\Support\Facades\Schema::hasColumn('departments', 'department_name')) {
                            $q->orWhere('d.department_name', 'ilike', '%' . $keyword . '%');
                        }
                    }
                });

            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                $query->where(function ($q) {
                    $q->where('u.status', 'active')->orWhere('u.status', 1)->orWhereNull('u.status');
                });
            }

            return $query->pluck('u.id');
        } catch (\Throwable $e) {
            return collect();
        }
    }

        private static function wmcNotificationAdminUsers(): \Illuminate\Support\Collection
    {
        return self::wmcNotificationUsersByRoleKeywords(['admin', 'bod', 'super']);
    }

    /**
     * Compatibility helper used by newer notification hooks.
     * Safely creates a notification row without requiring older service internals.
     */
    public static function createForUser(
        int $userId,
        string $module,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $relatedType = null,
        $relatedId = null,
        ?int $createdBy = null
    ): void {
        if ($userId <= 0) {
            return;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('system_notifications')) {
            return;
        }

        $data = [
            'user_id' => $userId,
            'module' => $module,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $optionalColumns = [
            'action_url' => $actionUrl,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'created_by' => $createdBy,
        ];

        foreach ($optionalColumns as $column => $value) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('system_notifications', $column)) {
                $data[$column] = $value;
            }
        }

        // Avoid duplicate unread notifications for the same user/event/type.
        $query = \Illuminate\Support\Facades\DB::table('system_notifications')
            ->where('user_id', $userId)
            ->where('type', $type);

        if ($relatedType !== null && \Illuminate\Support\Facades\Schema::hasColumn('system_notifications', 'related_type')) {
            $query->where('related_type', $relatedType);
        }

        if ($relatedId !== null && \Illuminate\Support\Facades\Schema::hasColumn('system_notifications', 'related_id')) {
            $query->where('related_id', $relatedId);
        }

        if ($query->exists()) {
            return;
        }

        \Illuminate\Support\Facades\DB::table('system_notifications')->insert($data);
    }

    public static function notifyPurchaseOrderStockInReceived($purchaseOrderId, $receivingId = null, ?int $actorId = null): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('purchase_orders')) {
                return;
            }

            $po = \Illuminate\Support\Facades\DB::table('purchase_orders')
                ->where('id', (int) $purchaseOrderId)
                ->first();

            if (! $po) {
                \Illuminate\Support\Facades\Log::warning('WMC PO stock-in notification skipped: PO not found', [
                    'purchase_order_id' => $purchaseOrderId,
                ]);
                return;
            }

            $poNo = $po->po_no ?? ('PO #' . $po->id);
            $status = strtolower((string) ($po->status ?? ''));

            $supplierName = 'supplier';

            try {
                if (! empty($po->supplier_id)) {
                    foreach (['suppliers', 'warehouse_suppliers'] as $supplierTable) {
                        if (\Illuminate\Support\Facades\Schema::hasTable($supplierTable)) {
                            $supplier = \Illuminate\Support\Facades\DB::table($supplierTable)
                                ->where('id', $po->supplier_id)
                                ->first();

                            if ($supplier) {
                                $supplierName = $supplier->name
                                    ?? $supplier->supplier_name
                                    ?? $supplier->company_name
                                    ?? 'supplier';
                                break;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                $supplierName = 'supplier';
            }

            $title = in_array($status, ['received', 'completed', 'fully_received'], true)
                ? 'PO Fully Received'
                : 'PO Partially Received';

            $directUsers = collect();

            foreach (['created_by', 'approved_by', 'requested_by', 'prepared_by', 'ordered_by'] as $column) {
                if (isset($po->{$column}) && ! empty($po->{$column})) {
                    $directUsers->push((int) $po->{$column});
                }
            }

            $recipientIds = collect()
                ->merge(self::wmcResolveInvolvedUserIds([
                    'modules' => ['purchasing', 'procurement'],
                    'access_levels' => ['staff', 'manager', 'admin', 'approver'],
                ]))
                ->merge($directUsers)
                ->filter(fn ($id) => (int) $id > 0 && (! $actorId || (int) $id !== (int) $actorId))
                ->unique()
                ->values();

            \Illuminate\Support\Facades\Log::info('WMC PO stock-in notification recipients resolved', [
                'purchase_order_id' => $po->id,
                'recipients' => $recipientIds->all(),
            ]);

            if ($recipientIds->isEmpty()) {
                \Illuminate\Support\Facades\Log::warning('WMC PO stock-in notification skipped: no recipients found', [
                    'purchase_order_id' => $po->id,
                    'po_no' => $poNo,
                ]);
                return;
            }

            self::wmcCreateNotifications(
                $recipientIds,
                'purchasing',
                'purchase_order_stock_in_received',
                $title,
                trim($poNo . ' from ' . $supplierName . ' has been received in warehouse stock in.'),
                self::wmcRouteUrl('purchasing.receiving.index', [], '/purchasing/receiving'),
                'purchase_order',
                (int) $po->id,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }


    /**
     * WMC dynamic recipient resolver foundation.
     * Uses current user_module_assignments table first, then users.primary_module,
     * then legacy user_module_accesses if present.
     */
    private static function wmcResolveInvolvedUserIds(array $filters = []): \Illuminate\Support\Collection
    {
        try {
            $modules = collect($filters['modules'] ?? [])
                ->map(fn ($module) => strtolower(trim((string) $module)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $accessLevels = collect($filters['access_levels'] ?? [])
                ->map(fn ($level) => strtolower(trim((string) $level)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $departmentKeywords = collect($filters['department_keywords'] ?? [])
                ->map(fn ($keyword) => strtolower(trim((string) $keyword)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $roleKeywords = collect($filters['role_keywords'] ?? [])
                ->map(fn ($keyword) => strtolower(trim((string) $keyword)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $branchId = $filters['branch_id'] ?? null;
            $excludeUserId = $filters['exclude_user_id'] ?? null;

            $userIds = collect();

            if (! empty($modules)) {
                $userIds = $userIds->merge(self::wmcNotificationUsersByModules($modules, $accessLevels));
            }

            if (! empty($departmentKeywords)) {
                $userIds = $userIds->merge(self::wmcNotificationUsersByDepartmentKeywords($departmentKeywords));
            }

            if (! empty($roleKeywords)) {
                $userIds = $userIds->merge(self::wmcNotificationUsersByRoleKeywords($roleKeywords));
            }

            $userIds = $userIds->filter()->unique()->values();

            if ($branchId !== null && $branchId !== '') {
                $userIds = $userIds->filter(function ($userId) use ($branchId) {
                    $user = \Illuminate\Support\Facades\DB::table('users')->where('id', (int) $userId)->first();
                    return $user && (int) ($user->branch_id ?? 0) === (int) $branchId;
                })->values();
            }

            if ($excludeUserId) {
                $userIds = $userIds->reject(fn ($userId) => (int) $userId === (int) $excludeUserId)->values();
            }

            return $userIds->values();
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }

    private static function wmcNotificationUsersByRoleKeywords(array $keywords): \Illuminate\Support\Collection
    {
        try {
            $keywords = collect($keywords)
                ->map(fn ($keyword) => strtolower(trim((string) $keyword)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($keywords) || ! \Illuminate\Support\Facades\Schema::hasTable('roles') || ! \Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
                return collect();
            }

            $query = \Illuminate\Support\Facades\DB::table('model_has_roles as mhr')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->join('users as u', 'u.id', '=', 'mhr.model_id')
                ->where('mhr.model_type', 'like', '%User%')
                ->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('r.name', 'ilike', '%' . $keyword . '%');
                    }
                });

            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                $query->where(function ($q) {
                    $q->whereNull('u.status')
                        ->orWhere('u.status', 'active')
                        ->orWhere('u.status', 1)
                        ->orWhere('u.status', true)
                        ->orWhere('u.status', '1');
                });
            }

            return $query->pluck('u.id')->filter()->unique()->values();
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }

    private static function wmcCreateNotifications($userIds, string $module, string $type, string $title, string $message, string $actionUrl, string $relatedType, int $relatedId, ?int $actorId = null): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('system_notifications')) {
                return;
            }

            foreach (collect($userIds)->filter()->unique()->values() as $userId) {
                $userId = (int) $userId;

                if ($userId <= 0) {
                    continue;
                }

                if ($actorId && $userId === (int) $actorId) {
                    continue;
                }

                self::createForUser(
                    userId: $userId,
                    module: $module,
                    type: $type,
                    title: $title,
                    message: $message,
                    actionUrl: $actionUrl,
                    relatedType: $relatedType,
                    relatedId: $relatedId,
                    createdBy: $actorId
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function notifyPurchaseOrderSupplierArrived($purchaseOrder, ?int $actorId = null): void
    {
        try {
            if (! $purchaseOrder) {
                return;
            }

            $poId = (int) ($purchaseOrder->id ?? 0);
            if ($poId <= 0) {
                return;
            }

            $poNo = (string) ($purchaseOrder->po_no ?? ('PO #' . $poId));
            $supplierName = self::wmcPurchasingSupplierName($purchaseOrder->supplier_id ?? null) ?: 'supplier';

            $recipients = self::wmcActionNotificationUserIds(
                ['warehouse', 'inventory'],
                ['staff', 'manager', 'admin', 'receiver'],
                $actorId
            );

            self::notifyUsers(
                $recipients,
                'warehouse',
                'purchase_order_supplier_arrived',
                'PO Stocks Arrived',
                $poNo . ' from ' . $supplierName . ' has physically arrived and is ready for warehouse receiving.',
                url('/warehouse/stock-in?purchase_order_id=' . $poId),
                'purchase_order',
                $poId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function wmcActionNotificationUserIds(array $modules, array $accessLevels = [], ?int $excludeUserId = null): \Illuminate\Support\Collection
    {
        try {
            $modules = collect($modules)
                ->map(fn ($module) => strtolower(trim((string) $module)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $accessLevels = collect($accessLevels ?: ['staff', 'manager', 'admin', 'receiver', 'approver'])
                ->map(fn ($level) => strtolower(trim((string) $level)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($modules)) {
                return collect();
            }

            $ids = collect();

            if (\Illuminate\Support\Facades\Schema::hasTable('user_module_assignments')) {
                $query = \Illuminate\Support\Facades\DB::table('user_module_assignments as uma')
                    ->join('users as u', 'u.id', '=', 'uma.user_id')
                    ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.module))'), $modules);

                if (\Illuminate\Support\Facades\Schema::hasColumn('user_module_assignments', 'access_level')) {
                    $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.access_level))'), $accessLevels);
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                    $query->where(function ($q) {
                        $q->where('u.status', 'active')
                            ->orWhere('u.status', 1)
                            ->orWhere('u.status', true)
                            ->orWhere('u.status', '1')
                            ->orWhereNull('u.status');
                    });
                }

                $ids = $ids->merge($query->pluck('u.id'));
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'primary_module')) {
                $query = \Illuminate\Support\Facades\DB::table('users as u')
                    ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(u.primary_module))'), $modules);

                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                    $query->where(function ($q) {
                        $q->where('u.status', 'active')
                            ->orWhere('u.status', 1)
                            ->orWhere('u.status', true)
                            ->orWhere('u.status', '1')
                            ->orWhereNull('u.status');
                    });
                }

                $ids = $ids->merge($query->pluck('u.id'));
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('user_module_accesses')) {
                $query = \Illuminate\Support\Facades\DB::table('user_module_accesses as uma')
                    ->join('users as u', 'u.id', '=', 'uma.user_id')
                    ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.module))'), $modules);

                if (\Illuminate\Support\Facades\Schema::hasColumn('user_module_accesses', 'access_level')) {
                    $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(uma.access_level))'), $accessLevels);
                }

                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                    $query->where(function ($q) {
                        $q->where('u.status', 'active')
                            ->orWhere('u.status', 1)
                            ->orWhere('u.status', true)
                            ->orWhere('u.status', '1')
                            ->orWhereNull('u.status');
                    });
                }

                $ids = $ids->merge($query->pluck('u.id'));
            }

            $ids = $ids->filter()->unique()->values();

            if ($excludeUserId) {
                $ids = $ids->reject(fn ($id) => (int) $id === (int) $excludeUserId)->values();
            }

            return $ids;
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }

    public static function notifySalesInvoiceCreated($invoice, ?int $actorId = null): void
    {
        try {
            if (! $invoice) {
                return;
            }

            $invoiceId = (int) ($invoice->id ?? 0);
            if ($invoiceId <= 0) {
                return;
            }

            $invoiceNo = (string) ($invoice->invoice_no ?? ('Invoice #' . $invoiceId));
            $customerName = self::wmcSalesCustomerName($invoice->customer_id ?? null);

            $directUsers = collect([
                $invoice->created_by ?? null,
            ])->filter();

            $recipients = collect()
                ->merge(self::wmcActionNotificationUserIds(['sales'], ['staff', 'manager', 'admin'], $actorId))
                ->merge(self::wmcActionNotificationUserIds(['accounting', 'finance'], ['staff', 'manager', 'admin', 'approver'], $actorId))
                ->merge($directUsers)
                ->filter(fn ($id) => (int) $id > 0 && (! $actorId || (int) $id !== (int) $actorId))
                ->unique()
                ->values();

            self::notifyUsers(
                $recipients,
                'sales',
                'sales_invoice_created',
                'Sales Invoice Created',
                trim($invoiceNo . ($customerName ? ' for ' . $customerName : '') . ' was created and posted to receivables.'),
                url('/sales/invoices/' . $invoiceId),
                'sales_invoice',
                $invoiceId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function notifySalesReceiptCreated($salesReceipt, ?int $actorId = null): void
    {
        try {
            if (! $salesReceipt) {
                return;
            }

            $receiptId = (int) ($salesReceipt->id ?? 0);
            if ($receiptId <= 0) {
                return;
            }

            $receiptNo = (string) ($salesReceipt->receipt_no ?? ('Sales Receipt #' . $receiptId));
            $customerName = self::wmcSalesCustomerName($salesReceipt->customer_id ?? null);

            $directUsers = collect([
                $salesReceipt->created_by ?? null,
            ])->filter();

            $recipients = collect()
                ->merge(self::wmcActionNotificationUserIds(['sales'], ['staff', 'manager', 'admin'], $actorId))
                ->merge(self::wmcActionNotificationUserIds(['accounting', 'finance'], ['staff', 'manager', 'admin', 'approver'], $actorId))
                ->merge($directUsers)
                ->filter(fn ($id) => (int) $id > 0 && (! $actorId || (int) $id !== (int) $actorId))
                ->unique()
                ->values();

            self::notifyUsers(
                $recipients,
                'sales',
                'sales_receipt_created',
                'Sales Receipt Created',
                trim($receiptNo . ($customerName ? ' for ' . $customerName : '') . ' was created and posted as paid sale.'),
                url('/sales/sales-receipts/' . $receiptId),
                'sales_receipt',
                $receiptId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function wmcSalesCustomerName($customerId): ?string
    {
        try {
            if (! $customerId || ! \Illuminate\Support\Facades\Schema::hasTable('customers')) {
                return null;
            }

            $customer = \Illuminate\Support\Facades\DB::table('customers')->where('id', $customerId)->first();

            return $customer->customer_name ?? $customer->name ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function notifyReceivePaymentCreated($payment, ?int $actorId = null): void
    {
        try {
            if (is_object($payment) && method_exists($payment, 'loadMissing')) {
                $payment->loadMissing(['customer', 'invoice']);
            }

            $paymentId = (int) ($payment->id ?? 0);
            $paymentNo = (string) ($payment->payment_no ?? ('Payment #' . $paymentId));
            $amount = number_format((float) ($payment->amount ?? 0), 2);
            $customerName = trim((string) data_get($payment, 'customer.name', data_get($payment, 'customer.customer_name', 'Customer')));
            $invoiceNo = trim((string) data_get($payment, 'invoice.invoice_no', ''));

            $title = 'Receive Payment Recorded';

            $message = $paymentNo . ' amounting to PHP ' . $amount . ' was recorded';
            if ($invoiceNo !== '') {
                $message .= ' for Invoice ' . $invoiceNo;
            }
            if ($customerName !== '') {
                $message .= ' - ' . $customerName;
            }
            $message .= '.';

            $actionUrl = null;

            try {
                if (\Illuminate\Support\Facades\Route::has('sales.receive-payments.index')) {
                    $actionUrl = route('sales.receive-payments.index');
                } elseif (\Illuminate\Support\Facades\Route::has('sales.invoices.index')) {
                    $actionUrl = route('sales.invoices.index');
                }
            } catch (\Throwable $e) {
                $actionUrl = null;
            }

            $roleNames = [
                'Super Admin',
                'Super Administrator',
                'Admin',
                'BOD',
                'Board of Directors',
                'super-admin',
                'admin',
                'bod',
                'Accounting',
                'accounting',
                'Sales',
                'sales',
            ];

            $userIds = collect();

            if (class_exists(\App\Models\User::class)) {
                try {
                    $userIds = \App\Models\User::query()
                        ->whereHas('roles', function ($query) use ($roleNames) {
                            $query->whereIn('name', $roleNames);
                        })
                        ->pluck('id');
                } catch (\Throwable $e) {
                    $userIds = collect();
                }

                if ($userIds->isEmpty()) {
                    try {
                        $query = \App\Models\User::query();

                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
                            $query->whereIn('role', $roleNames);
                            $userIds = $query->pluck('id');
                        }
                    } catch (\Throwable $e) {
                        $userIds = collect();
                    }
                }
            }

            $userIds = $userIds
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0 && (int) $id !== (int) $actorId)
                ->unique()
                ->values()
                ->all();

            if (empty($userIds)) {
                return;
            }

            self::notifyUsers(
                $userIds,
                'sales',
                'receive_payment_created',
                $title,
                $message,
                $actionUrl,
                'sales_payment',
                $paymentId ?: null,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }


    public static function notifyPurchaseBillPaymentCreated($payment, ?int $actorId = null): void
    {
        try {
            if (is_object($payment) && method_exists($payment, 'loadMissing')) {
                try {
                    $payment->loadMissing([
                        'purchaseBill.purchaseOrder',
                        'purchaseBill.supplier',
                        'accountingBankAccount',
                        'journalEntry',
                    ]);
                } catch (\Throwable $e) {
                    try {
                        $payment->loadMissing(['purchaseBill.purchaseOrder', 'purchaseBill.supplier']);
                    } catch (\Throwable $ignored) {
                        // Keep notification safe even if a relationship name differs.
                    }
                }
            }

            $paymentId = (int) ($payment->id ?? 0);
            if ($paymentId <= 0) {
                return;
            }

            $paymentNo = (string) ($payment->payment_no ?? ('Payment #' . $paymentId));
            $amount = number_format((float) ($payment->amount ?? 0), 2);
            $billNo = trim((string) data_get($payment, 'purchaseBill.bill_no', ''));
            $poNo = trim((string) data_get($payment, 'purchaseBill.purchaseOrder.po_no', ''));
            $supplierName = trim((string) data_get($payment, 'purchaseBill.supplier.name', data_get($payment, 'purchaseBill.supplier.supplier_name', '')));

            $title = 'Purchase Bill Payment Posted';

            $message = $paymentNo . ' amounting to PHP ' . $amount . ' was posted';
            if ($billNo !== '') {
                $message .= ' for Bill ' . $billNo;
            }
            if ($poNo !== '') {
                $message .= ' / PO ' . $poNo;
            }
            if ($supplierName !== '') {
                $message .= ' - ' . $supplierName;
            }
            $message .= '.';

            $actionUrl = null;

            try {
                if (\Illuminate\Support\Facades\Route::has('accounting.pay-bills.show')) {
                    $actionUrl = route('accounting.pay-bills.show', $paymentId);
                } elseif (\Illuminate\Support\Facades\Route::has('accounting.pay-bills.index')) {
                    $actionUrl = route('accounting.pay-bills.index');
                }
            } catch (\Throwable $e) {
                $actionUrl = url('/accounting/pay-bills');
            }

            $userIds = collect();

            try {
                if (method_exists(self::class, 'wmcActionNotificationUserIds')) {
                    $userIds = $userIds
                        ->merge(self::wmcActionNotificationUserIds(['accounting'], ['staff', 'manager', 'admin', 'approver', 'accounting'], $actorId))
                        ->merge(self::wmcActionNotificationUserIds(['purchasing'], ['staff', 'manager', 'admin', 'approver', 'purchasing'], $actorId));
                }
            } catch (\Throwable $e) {
                // Fallback to role-based users below.
            }

            $roleNames = [
                'Super Admin',
                'Super Administrator',
                'Admin',
                'BOD',
                'Board of Directors',
                'super-admin',
                'admin',
                'bod',
                'Accounting',
                'accounting',
                'Purchasing',
                'purchasing',
                'Procurement',
                'procurement',
            ];

            if (class_exists(\App\Models\User::class)) {
                try {
                    $roleUserIds = \App\Models\User::query()
                        ->whereHas('roles', function ($query) use ($roleNames) {
                            $query->whereIn('name', $roleNames);
                        })
                        ->pluck('id');

                    $userIds = $userIds->merge($roleUserIds);
                } catch (\Throwable $e) {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
                            $roleUserIds = \App\Models\User::query()
                                ->whereIn('role', $roleNames)
                                ->pluck('id');

                            $userIds = $userIds->merge($roleUserIds);
                        }
                    } catch (\Throwable $ignored) {
                        // No fallback users found.
                    }
                }
            }

            $userIds = $userIds
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0 && (int) $id !== (int) $actorId)
                ->unique()
                ->values()
                ->all();

            if (empty($userIds)) {
                return;
            }

            self::notifyUsers(
                $userIds,
                'accounting',
                'purchase_bill_payment_created',
                $title,
                $message,
                $actionUrl,
                'purchase_payment',
                $paymentId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }


    public static function notifyAccountingJournalEntryActivity($journalEntry, string $event, ?int $actorId = null): void
    {
        try {
            $entryId = (int) ($journalEntry->id ?? 0);
            if ($entryId <= 0) {
                return;
            }

            $entryNo = (string) ($journalEntry->entry_no ?? ('Journal Entry #' . $entryId));
            $description = trim((string) ($journalEntry->description ?? ''));
            $event = strtolower(trim($event));

            $titles = [
                'created' => 'Journal Entry Created',
                'posted' => 'Journal Entry Posted',
                'voided' => 'Journal Entry Voided',
            ];

            $types = [
                'created' => 'accounting_journal_entry_created',
                'posted' => 'accounting_journal_entry_posted',
                'voided' => 'accounting_journal_entry_voided',
            ];

            $title = $titles[$event] ?? 'Journal Entry Updated';
            $type = $types[$event] ?? 'accounting_journal_entry_updated';

            $message = $entryNo . ' was ' . $event;
            if ($description !== '') {
                $message .= ' - ' . $description;
            }
            $message .= '.';

            $actionUrl = null;
            try {
                if (\Illuminate\Support\Facades\Route::has('accounting.journal-entries.show')) {
                    $actionUrl = route('accounting.journal-entries.show', $entryId);
                } elseif (\Illuminate\Support\Facades\Route::has('accounting.journal-entries.index')) {
                    $actionUrl = route('accounting.journal-entries.index');
                }
            } catch (\Throwable $e) {
                $actionUrl = url('/accounting/journal-entries');
            }

            $userIds = collect();

            try {
                if (method_exists(self::class, 'wmcActionNotificationUserIds')) {
                    $userIds = $userIds->merge(self::wmcActionNotificationUserIds(
                        ['accounting'],
                        ['staff', 'manager', 'admin', 'approver', 'accounting'],
                        $actorId
                    ));
                }
            } catch (\Throwable $e) {
                // Continue to role fallback.
            }

            try {
                if (method_exists(self::class, 'wmcNotificationAdminUsers')) {
                    $userIds = $userIds->merge(self::wmcNotificationAdminUsers());
                }
            } catch (\Throwable $e) {
                // Continue to role fallback.
            }

            $roleNames = [
                'Super Admin', 'Super Administrator', 'Admin', 'BOD', 'Board of Directors',
                'super-admin', 'admin', 'bod', 'Accounting', 'accounting',
            ];

            if (class_exists(\App\Models\User::class)) {
                try {
                    $userIds = $userIds->merge(
                        \App\Models\User::query()
                            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roleNames))
                            ->pluck('id')
                    );
                } catch (\Throwable $e) {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
                            $userIds = $userIds->merge(
                                \App\Models\User::query()->whereIn('role', $roleNames)->pluck('id')
                            );
                        }
                    } catch (\Throwable $ignored) {
                        // No fallback users found.
                    }
                }
            }

            $userIds = $userIds
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0 && (int) $id !== (int) $actorId)
                ->unique()
                ->values()
                ->all();

            if (empty($userIds)) {
                return;
            }

            self::notifyUsers(
                $userIds,
                'accounting',
                $type,
                $title,
                $message,
                $actionUrl,
                'accounting_journal_entry',
                $entryId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function notifyAccountingExpenseActivity($expense, string $event, ?int $actorId = null): void
    {
        try {
            $expenseId = (int) ($expense->id ?? 0);
            if ($expenseId <= 0) {
                return;
            }

            $expenseNo = (string) ($expense->expense_no ?? ('Expense #' . $expenseId));
            $amount = number_format((float) ($expense->amount ?? 0), 2);
            $payee = trim((string) ($expense->payee ?? ''));
            $event = strtolower(trim($event));

            $titles = [
                'created' => 'Expense Payment Posted',
                'voided' => 'Expense Payment Voided',
            ];

            $types = [
                'created' => 'accounting_expense_created',
                'voided' => 'accounting_expense_voided',
            ];

            $title = $titles[$event] ?? 'Expense Payment Updated';
            $type = $types[$event] ?? 'accounting_expense_updated';

            $message = $expenseNo . ' amounting to PHP ' . $amount . ' was ' . $event;
            if ($payee !== '') {
                $message .= ' for ' . $payee;
            }
            $message .= '.';

            $actionUrl = null;
            try {
                if (\Illuminate\Support\Facades\Route::has('accounting.expenses.show')) {
                    $actionUrl = route('accounting.expenses.show', $expenseId);
                } elseif (\Illuminate\Support\Facades\Route::has('accounting.expenses.index')) {
                    $actionUrl = route('accounting.expenses.index');
                }
            } catch (\Throwable $e) {
                $actionUrl = url('/accounting/expenses');
            }

            $userIds = collect();

            try {
                if (method_exists(self::class, 'wmcActionNotificationUserIds')) {
                    $userIds = $userIds->merge(self::wmcActionNotificationUserIds(
                        ['accounting'],
                        ['staff', 'manager', 'admin', 'approver', 'accounting'],
                        $actorId
                    ));
                }
            } catch (\Throwable $e) {
                // Continue to role fallback.
            }

            try {
                if (method_exists(self::class, 'wmcNotificationAdminUsers')) {
                    $userIds = $userIds->merge(self::wmcNotificationAdminUsers());
                }
            } catch (\Throwable $e) {
                // Continue to role fallback.
            }

            $roleNames = [
                'Super Admin', 'Super Administrator', 'Admin', 'BOD', 'Board of Directors',
                'super-admin', 'admin', 'bod', 'Accounting', 'accounting',
            ];

            if (class_exists(\App\Models\User::class)) {
                try {
                    $userIds = $userIds->merge(
                        \App\Models\User::query()
                            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roleNames))
                            ->pluck('id')
                    );
                } catch (\Throwable $e) {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
                            $userIds = $userIds->merge(
                                \App\Models\User::query()->whereIn('role', $roleNames)->pluck('id')
                            );
                        }
                    } catch (\Throwable $ignored) {
                        // No fallback users found.
                    }
                }
            }

            $userIds = $userIds
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0 && (int) $id !== (int) $actorId)
                ->unique()
                ->values()
                ->all();

            if (empty($userIds)) {
                return;
            }

            self::notifyUsers(
                $userIds,
                'accounting',
                $type,
                $title,
                $message,
                $actionUrl,
                'accounting_expense',
                $expenseId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function notifyAccountingCollectionActivity($collection, string $event, ?int $actorId = null): void
    {
        try {
            $collectionId = (int) ($collection->id ?? 0);
            if ($collectionId <= 0) {
                return;
            }

            $collectionNo = (string) ($collection->collection_no ?? ('Collection #' . $collectionId));
            $amount = number_format((float) ($collection->amount ?? 0), 2);
            $payer = trim((string) ($collection->payer ?? ''));
            $event = strtolower(trim($event));

            $titles = [
                'created' => 'Collection Posted',
                'voided' => 'Collection Voided',
            ];

            $types = [
                'created' => 'accounting_collection_created',
                'voided' => 'accounting_collection_voided',
            ];

            $title = $titles[$event] ?? 'Collection Updated';
            $type = $types[$event] ?? 'accounting_collection_updated';

            $message = $collectionNo . ' amounting to PHP ' . $amount . ' was ' . $event;
            if ($payer !== '') {
                $message .= ' from ' . $payer;
            }
            $message .= '.';

            $actionUrl = null;
            try {
                if (\Illuminate\Support\Facades\Route::has('accounting.collections.show')) {
                    $actionUrl = route('accounting.collections.show', $collectionId);
                } elseif (\Illuminate\Support\Facades\Route::has('accounting.collections.index')) {
                    $actionUrl = route('accounting.collections.index');
                }
            } catch (\Throwable $e) {
                $actionUrl = url('/accounting/collections');
            }

            $userIds = collect();

            try {
                if (method_exists(self::class, 'wmcActionNotificationUserIds')) {
                    $userIds = $userIds->merge(self::wmcActionNotificationUserIds(
                        ['accounting'],
                        ['staff', 'manager', 'admin', 'approver', 'accounting'],
                        $actorId
                    ));
                }
            } catch (\Throwable $e) {
                // Continue to role fallback.
            }

            try {
                if (method_exists(self::class, 'wmcNotificationAdminUsers')) {
                    $userIds = $userIds->merge(self::wmcNotificationAdminUsers());
                }
            } catch (\Throwable $e) {
                // Continue to role fallback.
            }

            $roleNames = [
                'Super Admin', 'Super Administrator', 'Admin', 'BOD', 'Board of Directors',
                'super-admin', 'admin', 'bod', 'Accounting', 'accounting',
            ];

            if (class_exists(\App\Models\User::class)) {
                try {
                    $userIds = $userIds->merge(
                        \App\Models\User::query()
                            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roleNames))
                            ->pluck('id')
                    );
                } catch (\Throwable $e) {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
                            $userIds = $userIds->merge(
                                \App\Models\User::query()->whereIn('role', $roleNames)->pluck('id')
                            );
                        }
                    } catch (\Throwable $ignored) {
                        // No fallback users found.
                    }
                }
            }

            $userIds = $userIds
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0 && (int) $id !== (int) $actorId)
                ->unique()
                ->values()
                ->all();

            if (empty($userIds)) {
                return;
            }

            self::notifyUsers(
                $userIds,
                'accounting',
                $type,
                $title,
                $message,
                $actionUrl,
                'accounting_collection',
                $collectionId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }


    public static function notifyChatMessageCreated($message, int $recipientId, ?int $actorId = null): void
    {
        try {
            if ($recipientId <= 0 || ($actorId && (int) $recipientId === (int) $actorId)) {
                return;
            }

            if (is_object($message) && method_exists($message, 'loadMissing')) {
                $message->loadMissing(['sender', 'conversation']);
            }

            $messageId = (int) ($message->id ?? 0);
            $conversationId = (int) ($message->chat_conversation_id ?? data_get($message, 'conversation.id', 0));

            if ($messageId <= 0 || $conversationId <= 0) {
                return;
            }

            $senderName = trim((string) (
                data_get($message, 'sender.full_name')
                ?: data_get($message, 'sender.email')
                ?: 'User'
            ));

            $body = trim((string) ($message->body ?? ''));
            $snippet = \Illuminate\Support\Str::limit($body, 90);

            $actionUrl = null;

            try {
                if (\Illuminate\Support\Facades\Route::has('chat.show')) {
                    $actionUrl = route('chat.show', $conversationId);
                }
            } catch (\Throwable $e) {
                $actionUrl = url('/chat/' . $conversationId);
            }

            self::notifyUser(
                $recipientId,
                'chat',
                'chat_message_created',
                'New Chat Message',
                $senderName . ': ' . $snippet,
                $actionUrl,
                'chat_message',
                $messageId,
                $actorId
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

}








