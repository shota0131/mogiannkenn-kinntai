<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に選択したデータが表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => 'ユーザー1']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-28 09:00',
            'end_time' => '2025-11-28 18:00',
            'break_start_time' => '2025-11-28 12:00',
            'break_end_time' => '2025-11-28 13:00',
            'remarks' => '備考テスト',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('備考テスト');
    }

    /** @test */
    public function 出勤時間が退勤時間より後だとバリデーションエラーになる()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '18:00',
            'end_time' => '09:00',
            'remarks' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後だとバリデーションエラーになる()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => '19:00',
            'remarks' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['break_start_time']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後だとバリデーションエラーになる()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => '12:00',
            'break_end_time' => '19:00',
            'remarks' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['break_end_time']);
    }

    /** @test */
    public function 備考欄が未入力だとバリデーションエラーになる()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->put("/admin/attendance/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start_time' => '12:00',
            'break_end_time' => '13:00',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }
}
