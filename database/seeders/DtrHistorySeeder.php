<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DtrLog;
use App\Models\DailyTimeRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DtrHistorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure the user exists (Targeting ID 1 or the seed intern)
        $user = User::firstOrCreate(
            ['email' => 'seed_intern@example.com'],
            ['name' => 'Seed Intern', 'password' => bcrypt('password')]
        );

        // 2. Ensure intern_profile exists
        $profile = DB::table('intern_profiles')->where('name', $user->name)->first();
        if ($profile) {
            $internId = $profile->id;
        } else {
            $internId = DB::table('intern_profiles')->insertGetId([
                'name' => $user->name,
                'department' => 'IT Department',
                'position' => 'Intern',
                'required_hours' => 720,
                'dtr_mode' => 'split',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Define the date range to hit exactly 720 hours (90 days x 8 hours)
        // From Dec 22, 2025 to April 4, 2026
        $period = CarbonPeriod::create('2025-12-22', '2026-04-04');

        foreach ($period as $date) {
            // Skip Sundays
            if ($date->isSunday()) {
                continue;
            }

            // Standard 8-hour shift times
            $amIn = '08:00:00';
            $amOut = '12:00:00';
            $pmIn = '13:00:00';
            $pmOut = '17:00:00';
            $total = 8.00;

            // 4. Update or Create DtrLog
            DtrLog::updateOrCreate(
                [
                    'intern_id' => $internId,
                    'log_date' => $date->format('Y-m-d'),
                ],
                [
                    'am_in' => $amIn,
                    'am_out' => $amOut,
                    'pm_in' => $pmIn,
                    'pm_out' => $pmOut,
                    'daily_total_hours' => $total,
                ]
            );

            // 5. Update or Create DailyTimeRecord
            DailyTimeRecord::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => $date->format('Y-m-d'),
                ],
                [
                    'am_in' => $amIn,
                    'am_out' => $amOut,
                    'pm_in' => $pmIn,
                    'pm_out' => $pmOut,
                    'total_hours' => $total,
                ]
            );
        }
    }
}