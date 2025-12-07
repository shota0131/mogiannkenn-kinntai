<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminAttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = [
            '山田 太郎',
            '西 侑奈',
            '増田 一世',
            '山本 敬直',
            '中園 航夫',
        ];

        foreach ($users as $name) {
            $userModel = User::firstOrCreate(
                ['email' => str_replace(' ', '', $name) . '@example.com'],
                ['name' => $name, 'password' => bcrypt('password')]
            );

            $dates = [Carbon::yesterday(), Carbon::today(), Carbon::tomorrow()];

            foreach ($dates as $date) {
                Attendance::create([
                    'user_id'     => $userModel->id,
                    'date'        => $date->format('Y-m-d'),
                    'start_time'  => Carbon::parse($date->format('Y-m-d').' 09:00')->toDateTimeString(),
                    'end_time'    => Carbon::parse($date->format('Y-m-d').' 18:00')->toDateTimeString(),
                    'break_start' => Carbon::parse($date->format('Y-m-d').' 12:00')->toDateTimeString(),
                    'break_end'   => Carbon::parse($date->format('Y-m-d').' 13:00')->toDateTimeString(),
                    'work_time'   => '8:00',
                ]);
            }
        }
    }
}
