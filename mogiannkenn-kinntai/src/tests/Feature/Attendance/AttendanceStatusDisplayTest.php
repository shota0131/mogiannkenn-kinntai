<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外の場合、ステータスが正しく表示される
     */
    public function test_status_display_before_work()
    {
        $user = User::factory()->create();

        // ログイン
        $this->actingAs($user);

        // /attendance へアクセス
        $response = $this->get('/attendance');

        $response->assertStatus(200);

        // 「勤務外」が表示されること
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中の場合、ステータスが正しく表示される
     */
    public function test_status_display_working()
    {
        $user = User::factory()->create();

        // 出勤状態を作成（attendanceテーブルの仕様に合わせる）
        $user->attendance()->create([
            'start_time' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中の場合、ステータスが正しく表示される
     */
    public function test_status_display_on_break()
    {
        $user = User::factory()->create();

        $user->attendance()->create([
            'start_time' => now(),
            'break_start' => now(),
            'status' => 'on_break',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済の場合、ステータスが正しく表示される
     */
    public function test_status_display_after_work()
    {
        $user = User::factory()->create();

        $user->attendance()->create([
            'start_time' => now()->subHours(8),
            'end_time' => now(),
            'status' => 'after_work',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
