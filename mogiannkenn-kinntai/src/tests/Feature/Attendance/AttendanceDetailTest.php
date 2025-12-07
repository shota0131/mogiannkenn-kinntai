<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'name' => '山田太郎'
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'start_time'   => '2025-11-10 09:00',
            'end_time'     => '2025-11-10 18:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    /** @test */
    public function 勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'start_time'   => '2025-11-10 09:00',
            'end_time'     => '2025-11-10 18:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);

        // Blade 例： 2025年11月10日（Mon）
        $expectedDate = Carbon::parse('2025-11-10')->format('Y年n月j日（D）');

        $response->assertSee($expectedDate);
    }

    /** @test */
    public function 出勤退勤時間がログインユーザーの打刻と一致して表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'start_time'   => '2025-11-10 09:12',
            'end_time'     => '2025-11-10 18:34',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('09:12');
        $response->assertSee('18:34');
    }

    /** @test */
    public function 休憩に表示される時間がログインユーザーの休憩打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'start_time'   => '2025-11-10 09:00',
            'end_time'     => '2025-11-10 18:00',
        ]);

        // 休憩情報を2件登録
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time'    => '2025-11-10 12:00',
            'end_time'      => '2025-11-10 12:30',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time'    => '2025-11-10 15:00',
            'end_time'      => '2025-11-10 15:15',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);

        // 休憩時間がすべてページに表示されているか
        $response->assertSee('12:00');
        $response->assertSee('12:30');
        $response->assertSee('15:00');
        $response->assertSee('15:15');
    }
}
