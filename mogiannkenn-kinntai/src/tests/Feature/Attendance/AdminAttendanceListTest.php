<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 当日の全ユーザーの勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        // 今日の勤怠情報を登録
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'start_time' => '2025-11-28 09:00',
            'end_time' => '2025-11-28 18:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'start_time' => '2025-11-28 08:30',
            'end_time' => '2025-11-28 17:30',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance');

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('ユーザー2');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /** @test */
    public function 勤怠一覧画面に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 11, 28, 10, 0, 0));

        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/attendance');

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y年n月j日'));
    }

    /** @test */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'ユーザー1']);

        // 前日の勤怠
        Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-27 09:00',
            'end_time' => '2025-11-27 18:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance?date=2025-11-27');

        $response->assertStatus(200);
        $response->assertSee('2025年11月27日');
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'ユーザー1']);

        // 翌日の勤怠
        Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-29 09:00',
            'end_time' => '2025-11-29 18:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance?date=2025-11-29');

        $response->assertStatus(200);
        $response->assertSee('2025年11月29日');
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
