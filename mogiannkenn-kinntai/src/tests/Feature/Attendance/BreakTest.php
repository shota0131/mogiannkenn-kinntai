<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 休憩入ボタンが表示され休憩処理ができる()
    {
        $user = User::factory()->create();

        // 出勤中の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => null,
            'is_resting' => false,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        // ボタンが表示されている
        $response->assertSee('休憩入');

        // 休憩入の処理
        $post = $this->actingAs($user)->post('/attendance/rest/start');
        $post->assertStatus(302);

        $this->assertDatabaseHas('rest_records', [
            'attendance_id' => $attendance->id,
        ]);

        $attendance->refresh();
        $this->assertTrue($attendance->is_resting);
    }

    /** @test */
    public function 出勤中であれば休憩は何度でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => null,
            'is_resting' => false,
        ]);

        // 休憩入 → 休憩戻 → 休憩入 の流れ
        $this->actingAs($user)->post('/attendance/rest/start');
        $this->actingAs($user)->post('/attendance/rest/end');
        $this->actingAs($user)->post('/attendance/rest/start');

        // 再び休憩入が取れている
        $attendance->refresh();
        $this->assertTrue($attendance->is_resting);
    }

    /** @test */
    public function 休憩戻の処理が正しく動作する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => null,
            'is_resting' => true,
        ]);

        // 休憩戻
        $response = $this->actingAs($user)->post('/attendance/rest/end');
        $response->assertStatus(302);

        $attendance->refresh();
        $this->assertFalse($attendance->is_resting);
    }

    /** @test */
    public function 休憩戻は何度でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => null,
            'is_resting' => false,
        ]);

        // 休憩入 → 休憩戻 → 休憩入 → 休憩戻
        $this->actingAs($user)->post('/attendance/rest/start');
        $this->actingAs($user)->post('/attendance/rest/end');
        $this->actingAs($user)->post('/attendance/rest/start');
        $this->actingAs($user)->post('/attendance/rest/end');

        $attendance->refresh();
        $this->assertFalse($attendance->is_resting);
    }

    /** @test */
    public function 休憩時刻が勤怠一覧で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 28, 12, 00));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => null,
            'is_resting' => false,
        ]);

        // 休憩入
        $this->actingAs($user)->post('/attendance/rest/start');

        Carbon::setTestNow(Carbon::create(2025, 11, 28, 12, 30));

        // 休憩戻
        $this->actingAs($user)->post('/attendance/rest/end');

        // 一覧画面
        $response = $this->actingAs($user)->get('/attendance/list');

        // 12:00 〜 12:30 の休憩が表示されていること
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }
}
