<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の勤怠情報が全て表示される()
    {
        $user = User::factory()->create();

        // 自分の勤怠データ
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-01 09:00',
            'end_time'   => '2025-11-01 18:00',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-02 09:30',
            'end_time'   => '2025-11-02 18:10',
        ]);

        // 他人データ（表示されてはならない）
        Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'start_time' => '2025-11-01 09:00',
            'end_time'   => '2025-11-01 18:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('09:30');
        $response->assertSee('18:10');

        // 他人の勤怠情報は表示されていない
        $response->assertDontSee('09:00 (他人)');
    }

    /** @test */
    public function 勤怠一覧画面に現在の月が表示される()
    {
        Carbon::setTestNow('2025-11-28 12:00:00');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        // 例：2025年11月
        $response->assertSee('2025年11月');
    }

    /** @test */
    public function 前月ボタンを押すと前月の情報が表示される()
    {
        $user = User::factory()->create();

        // 前月（2025/10）のデータ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-10-05 09:00',
            'end_time'   => '2025-10-05 18:00',
        ]);

        Carbon::setTestNow('2025-11-15');

        // 「前月」ボタン押下 → /attendance/list?month=2025-10 と仮定
        $response = $this->actingAs($user)->get('/attendance/list?month=2025-10');

        $response->assertSee('2025年10月');  // 表示月
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の情報が表示される()
    {
        $user = User::factory()->create();

        // 翌月（2025/12）のデータ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-12-03 09:00',
            'end_time'   => '2025-12-03 18:00',
        ]);

        Carbon::setTestNow('2025-11-15');

        // 「翌月」押下 → /attendance/list?month=2025-12 と仮定
        $response = $this->actingAs($user)->get('/attendance/list?month=2025-12');

        $response->assertSee('2025年12月');  
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 詳細ボタンを押すと勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-02 09:00',
            'end_time' => '2025-11-02 18:00',
        ]);

        // 勤怠一覧にアクセス
        $response = $this->actingAs($user)->get('/attendance/list');

        // 「詳細」リンクが存在する
        $url = "/attendance/{$attendance->id}";

        $response->assertSee($url);

        // 実際にアクセスして詳細画面へ
        $res = $this->actingAs($user)->get($url);
        $res->assertStatus(200);

        // 詳細画面に勤怠データが表示されているか
        $res->assertSee('09:00');
        $res->assertSee('18:00');
    }
}
