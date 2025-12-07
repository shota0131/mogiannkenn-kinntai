<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // user作成 or 既存user使用
        $user = User::firstOrCreate();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // 前月 / 今月 / 翌月
        $months = [
            now()->subMonth()->format('Y-m'),
            now()->format('Y-m'),
            now()->addMonth()->format('Y-m'),
        ];

        foreach ($months as $month) {

            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

                // Attendance 登録
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'start_time' => $date->copy()->setTime(9, 0),
                    'end_time'   => $date->copy()->setTime(18, 0),
                ]);

                // BreakTime 登録（12:00〜13:00）
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $date->copy()->setTime(12, 0),
                    'end_time'   => $date->copy()->setTime(13, 0),
                ]);
            }
        }
    }
}
