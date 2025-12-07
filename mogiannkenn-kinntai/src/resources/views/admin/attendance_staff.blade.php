@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_staff.css') }}">
@endsection

@section('content')
<div class="staff-attendance-page">
    <div class="staff-attendance-container">

        <h2 class="page-title">{{ $staff->name }}さんの勤怠</h2>

        @php
            $current = \Carbon\Carbon::parse($month);
            $prev = $current->copy()->subMonth()->format('Y-m');
            $next = $current->copy()->addMonth()->format('Y-m');
            $week = ['日','月','火','水','木','金','土'];
        @endphp

        {{-- 月移動 --}}
        <div class="month-nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $prev]) }}" class="btn">◀ 前月</a>
            <span>{{ $current->format('Y年m月') }}</span>
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $next]) }}" class="btn">翌月 ▶</a>
        </div>

        {{-- ▼ 勤怠カード（テーブル） --}}
        <div class="attendance-card">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($attendances as $attendance)

                        @php
                            $date = \Carbon\Carbon::parse($attendance->date);

                            $start = $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time) : null;
                            $end = $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time) : null;

                            $breakStart = $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start) : null;
                            $breakEnd   = $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end) : null;

                            // 休憩時間（分）
                            $breakMinutes = 0;
                            if ($breakStart && $breakEnd) {
                                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                            }

                            // 休憩時間 (HH:MM)
                            $breakDisplay = '--:--';
                            if ($breakMinutes > 0) {
                                $breakDisplay = sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
                            }

                            // 合計時間
                            $total = '--:--';
                            if ($start && $end) {
                                $workMinutes = $end->diffInMinutes($start) - $breakMinutes;
                                if ($workMinutes >= 0) {
                                    $total = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                                }
                            }
                        @endphp

                        <tr>
                            <td>{{ $date->format('m/d') }}({{ $week[$date->dayOfWeek] }})</td>
                            <td>{{ $start ? $start->format('H:i') : '--:--' }}</td>
                            <td>{{ $end ? $end->format('H:i') : '--:--' }}</td>
                            <td>{{ $breakDisplay }}</td>
                            <td>{{ $total }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="detail-btn">詳細</a>
                            </td>
                        </tr>

                    @empty
                        <tr><td colspan="6">データがありません</td></tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- ▼ CSV 出力（カードの下 / 右下） --}}
        <div class="csv-area-bottom">
            <a href="{{ route('admin.attendance.exportCsv', ['id' => $staff->id, 'month' => $month]) }}" class="btn btn-green">
                CSV出力
            </a>
        </div>

    </div>
</div>
@endsection





