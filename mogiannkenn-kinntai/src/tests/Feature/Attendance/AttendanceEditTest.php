<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceEditTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '2025-11-10 09:00',
            'end_time' => '2025-11-10 18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'remarks' => '修正テスト',
        ]);

        $response->assertSessionHasErrors(['start_time' => '出勤時間が不適切な値です']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'break_start_time' => '19:00',
            'break_end_time' => '19:30',
            'remarks' => '修正テスト',
        ]);

        $response->assertSessionHasErrors(['break_start_time' => '休憩時間が不適切な値です']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'break_start_time' => '12:00',
            'break_end_time' => '19:00',
            'remarks' => '修正テスト',
        ]);

        $response->assertSessionHasErrors(['break_end_time' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /** @test */
    public function 備考欄が未入力だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks' => '備考を記入してください']);
    }

    /** @test */
    public function 修正申請処理が正しく実行される()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // 修正申請
        $response = $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '修正申請テスト',
        ]);

        $response->assertSessionHas('message');

        // 管理者で承認画面へアクセス（承認前）
        $this->actingAs($admin)->get('/admin/attendance/requests')
            ->assertSee('修正申請テスト');
    }

    /** @test */
    public function 承認待ちの申請が全てユーザーに表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // 修正申請
        $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '承認待ちテスト',
        ]);

        $response = $this->actingAs($user)->get('/attendance/requests');
        $response->assertSee('承認待ちテスト');
    }

    /** @test */
    public function 承認済みの申請が全て管理者で表示される()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // 修正申請
        $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '承認済みテスト',
        ]);

        // 管理者が承認
        $this->actingAs($admin)->post("/admin/attendance/approve/{$attendance->id}", [
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/approved');
        $response->assertSee('承認済みテスト');
    }

    /** @test */
    public function 各申請の詳細ボタンで勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // 修正申請
        $this->actingAs($user)->post("/attendance/{$attendance->id}/update", [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '詳細遷移テスト',
        ]);

        // 詳細画面へ遷移
        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('詳細遷移テスト');
    }
}
