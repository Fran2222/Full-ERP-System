$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$controller = "app\Http\Controllers\Sales\SalesDashboardController.php"

Copy-Item $controller "$controller.bak-bod-department-detection-$stamp" -Force

$content = Get-Content $controller -Raw

$newMethod = @'
    private function canFilterBranches($user): bool
    {
        if (! $user) {
            return false;
        }

        try {
            if (method_exists($user, 'loadMissing')) {
                $user->loadMissing(['roles', 'department']);
            }
        } catch (\Throwable $e) {
            // Continue with available user data.
        }

        $tokens = collect();

        try {
            if (method_exists($user, 'getRoleNames')) {
                $tokens = $tokens->merge($user->getRoleNames());
            } elseif (method_exists($user, 'roles')) {
                $tokens = $tokens->merge($user->roles()->pluck('name'));
            }
        } catch (\Throwable $e) {
            // Continue with fallback columns.
        }

        foreach (['role', 'user_type', 'access_level', 'position', 'designation'] as $field) {
            try {
                $value = $user->{$field} ?? null;
                if (! empty($value)) {
                    $tokens->push($value);
                }
            } catch (\Throwable $e) {
                // Ignore missing field.
            }
        }

        try {
            $departmentName = trim((string) data_get($user, 'department.name', ''));
            $departmentCode = trim((string) data_get($user, 'department.code', ''));
            if ($departmentName !== '') {
                $tokens->push($departmentName);
            }
            if ($departmentCode !== '') {
                $tokens->push($departmentCode);
            }
        } catch (\Throwable $e) {
            // Continue.
        }

        try {
            $departmentId = (int) ($user->department_id ?? 0);
            if ($departmentId > 0
                && \Illuminate\Support\Facades\Schema::hasTable('departments')) {
                $department = \Illuminate\Support\Facades\DB::table('departments')
                    ->where('id', $departmentId)
                    ->first();

                if ($department) {
                    foreach (['name', 'code', 'description'] as $column) {
                        if (isset($department->{$column}) && trim((string) $department->{$column}) !== '') {
                            $tokens->push($department->{$column});
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue.
        }

        $haystack = strtolower($tokens->filter()->implode(' '));

        return str_contains($haystack, 'bod')
            || str_contains($haystack, 'board of directors')
            || str_contains($haystack, 'super admin')
            || str_contains($haystack, 'super-admin')
            || str_contains($haystack, 'super administrator');
    }

'@

$pattern = '(?s)    private function canFilterBranches\(\$user\): bool\s*\{.*?\n    \}\s*\n\s*    private function resolvePeriod'

if ($content -notmatch $pattern) {
    throw "Could not find canFilterBranches() method in SalesDashboardController.php"
}

$content = [regex]::Replace($content, $pattern, $newMethod + "    private function resolvePeriod", 1)

Set-Content $controller $content -NoNewline

php -l $controller
php artisan view:clear
php artisan optimize:clear

Write-Host "BOD department detection applied."
Write-Host "Backup: $controller.bak-bod-department-detection-$stamp"