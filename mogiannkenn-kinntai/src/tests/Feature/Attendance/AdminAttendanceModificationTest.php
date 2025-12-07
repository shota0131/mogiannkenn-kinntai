<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceModificationRequest;

class AdminAttendanceModificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちの修正申請が全て表示される()
    {
        $admin = Admin::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user2->id]);

        AttendanceModificationRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'status' => 'pending',
        ]);
        AttendanceModificationRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance-modifications?tab=pending');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    /** @test */
    public function 承認済みの修正申請が全て表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $modification = AttendanceModificationRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance-modifications?tab=approved');

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $modification = AttendanceModificationRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'request_data' => [
                'start_time' => '09:30',
                'end_time' => '18:30',
            ],
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance-modifications/{$modification->id}");

        $response->assertStatus(200);
        $response->assertSee('09:30');
        $response->assertSee('18:30');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $modification = AttendanceModificationRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'request_data' => [
                'start_time' => '09:30',
                'end_time' => '18:30',
            ],
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance-modifications/{$modification->id}/approve");

        $response->assertRedirect(); // 承認後にリダイレクト
        $this->assertDatabaseHas('attendance_modification_requests', [
            'id' => $modification->id,
            'status' => 'approved',
        ]);

        // 勤怠情報が修正されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '2025-11-28 09:30:00',
            'end_time' => '2025-11-28 18:30:00',
        ]);
    }
}
