<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;

class AdminUserInfoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者は全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $admin = Admin::factory()->create();
        $user1 = User::factory()->create(['name' => 'ユーザー1', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['name' => 'ユーザー2', 'email' => 'user2@example.com']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('ユーザー2');
        $response->assertSee('user2@example.com');
    }

    /** @test */
    public function 選択したユーザーの勤怠情報が正確に表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'ユーザー1']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-28 09:00',
            'end_time' => '2025-11-28 18:00',
            'break_start_time' => '12:00',
            'break_end_time' => '13:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/attendance");

        $response->assertStatus(200);
        $response->assertSee('2025-11-28');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /** @test */
    public function 前月ボタンを押下した時に前月の勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendancePrev = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-10-15 09:00',
            'end_time' => '2025-10-15 18:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users/' . $user->id . '/attendance?month=2025-10');

        $response->assertStatus(200);
        $response->assertSee('2025-10-15');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌月ボタンを押下した時に翌月の勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendanceNext = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-12-05 09:00',
            'end_time' => '2025-12-05 18:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users/' . $user->id . '/attendance?month=2025-12');

        $response->assertStatus(200);
        $response->assertSee('2025-12-05');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 詳細ボタンを押下するとその日の勤怠詳細画面に遷移する()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-28 09:00',
            'end_time' => '2025-11-28 18:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
