<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class LeaveWorkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤ボタンが表示され退勤処理ができる()
    {
        $user = User::factory()->create();

        // 出勤中の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'start_time'   => now(),
            'end_time'     => null,
            'is_resting'   => false,
        ]);

        // 画面表示（退勤ボタンが出ているかチェック）
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        // 退勤処理
        $post = $this->actingAs($user)->post('/attendance/end');
        $post->assertStatus(302);

        // DBに退勤時刻が記録されている
        $attendance->refresh();
        $this->assertNotNull($attendance->end_time);

        // ステータスが退勤済になっている（is_resting 等を使っていない想定）
        $this->assertTrue($attendance->isFinished());
    }

    /** @test */
    public function 出勤と退勤を行うと勤怠一覧に退勤時刻が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 28, 9, 00));

        $user = User::factory()->create();

        // 出勤処理
        $this->actingAs($user)->post('/attendance/start');

        Carbon::setTestNow(Carbon::create(2025, 11, 28, 18, 00));

        // 退勤処理
        $this->actingAs($user)->post('/attendance/end');

        // 一覧画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/list');

        // 出勤9:00、退勤18:00 が表示されていることを確認
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
