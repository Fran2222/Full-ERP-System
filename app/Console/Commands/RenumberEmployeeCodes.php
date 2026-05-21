<?php

namespace App\Console\Commands;

use App\Models\EmployeeProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
    protected $description = 'Renumber employee codes by hire date from oldest hired to latest hired.';

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
                ->orderByRaw('employee_profiles.hire_date ASC NULLS LAST')
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

            foreach ($profiles->values() as $index => $profile) {
                $profile->forceFill([
                    'employee_id' => $this->formatEmployeeCode($index + 1),
                ])->save();
            }

            $count = $profiles->count();
        });

        $this->info("Employee codes renumbered successfully. Total updated: {$count}");

        return 0;
    }

    protected function formatEmployeeCode(int $number): string
    {
        return 'EMP-' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
