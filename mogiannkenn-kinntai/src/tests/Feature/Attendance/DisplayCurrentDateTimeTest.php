<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Support\Carbon;

class DisplayCurrentDateTimeTest extends TestCase
{
    /**
     * 出勤登録画面で現在の日時がUIと同じ形式で表示されることを確認
     */
    public function test_current_datetime_display_matches_ui_format()
    {
        // ★ テスト用に現在日時を固定
        Carbon::setTestNow(Carbon::create(2025, 11, 28, 16, 34, 0));

        // 出勤画面へアクセス
        $response = $this->get('/attendance');

        $response->assertStatus(200);

        // ★ Blade と同じフォーマットで期待値を生成
        $expectedDate = now()->format('Y年n月j日（D）');
        $expectedTime = now()->format('H:i');

        // ★ 日付と時刻が正しく表示されていることを確認
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}

