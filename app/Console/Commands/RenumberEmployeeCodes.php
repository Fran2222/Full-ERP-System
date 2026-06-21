<?php

namespace App\Console\Commands;

use App\Models\EmployeeProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RenumberEmployeeCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:renumber-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renumber employee codes by hire year and hire date sequence using EMP-YEAR-#### format.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = 0;

        DB::transaction(function () use (&$count) {
            $profiles = EmployeeProfile::query()
                ->join('users', 'users.id', '=', 'employee_profiles.user_id')
                ->select('employee_profiles.id', 'employee_profiles.employee_id', 'employee_profiles.hire_date')
                ->orderByRaw('CASE WHEN employee_profiles.hire_date IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('employee_profiles.hire_date', 'asc')
                ->orderByRaw("LOWER(COALESCE(users.last_name, '')) ASC")
                ->orderByRaw("LOWER(COALESCE(users.first_name, '')) ASC")
                ->orderBy('employee_profiles.id')
                ->lockForUpdate()
                ->get();

            $timestamp = now()->format('YmdHis');

            foreach ($profiles as $profile) {
                $profile->forceFill([
                    'employee_id' => 'TEMP-EMP-' . $profile->id . '-' . $timestamp,
                ])->save();
            }

            $yearCounters = [];

            foreach ($profiles as $profile) {
                $year = $this->employeeCodeYear($profile->hire_date ?? null);
                $yearCounters[$year] = ($yearCounters[$year] ?? 0) + 1;

                $profile->forceFill([
                    'employee_id' => $this->formatEmployeeCode($year, $yearCounters[$year]),
                ])->save();
            }

            $count = $profiles->count();
        });

        $this->info("Employee codes renumbered successfully using EMP-YEAR-#### format. Total updated: {$count}");

        return 0;
    }

    protected function employeeCodeYear($hireDate = null): string
    {
        if (blank($hireDate)) {
            return now()->format('Y');
        }

        try {
            return Carbon::parse($hireDate)->format('Y');
        } catch (\Throwable $e) {
            return now()->format('Y');
        }
    }

    protected function formatEmployeeCode(string $year, int $number): string
    {
        return 'EMP-' . $year . '-' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
