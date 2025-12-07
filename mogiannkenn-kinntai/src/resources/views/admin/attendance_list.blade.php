@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="admin-main">

    @php
        $carbonDate = \Carbon\Carbon::parse($date);
        $prevDate = $carbonDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $carbonDate->copy()->addDay()->format('Y-m-d');
        $displayDate = $carbonDate->format('Y/m/d');
        $titleDate = $carbonDate->format('Y年n月j日の勤怠');
    @endphp

    {{-- ===== タイトル表示 ===== --}}
    <div class="date-title">
        {{ $titleDate }}
    </div>

    {{-- ===== ナビカード ===== --}}
    <div class="nav-card">
        <div class="nav-inner">

            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="nav-side-btn">
                ← 前日
            </a>

            <div class="nav-center-date">
                <span class="calendar-icon">📅</span>
                <span class="date-text">{{ $displayDate }}</span>
            </div>

            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="nav-side-btn">
                翌日 →
            </a>

        </div>
    </div>

    {{-- ===== 勤怠一覧 ===== --}}
    <div class="attendance-card">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($attendances as $att)
                    @php
                        $start = $att->start_time ? \Carbon\Carbon::parse($att->start_time)->format('H:i') : '--:--';
                        $end   = $att->end_time   ? \Carbon\Carbon::parse($att->end_time)->format('H:i') : '--:--';

                        $breakMinutes = 0;
                        if ($att->break_start && $att->break_end) {
                            $breakMinutes = \Carbon\Carbon::parse($att->break_end)
                                ->diffInMinutes(\Carbon\Carbon::parse($att->break_start));
                        }
                        $break = $breakMinutes
                            ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                            : '--:--';

                        $total = '--:--';
                        if ($att->start_time && $att->end_time) {
                            $work = \Carbon\Carbon::parse($att->end_time)
                                    ->diffInMinutes(\Carbon\Carbon::parse($att->start_time))
                                    - $breakMinutes;

                            if ($work > 0) {
                                $total = sprintf('%d:%02d', floor($work / 60), $work % 60);
                            }
                        }
                    @endphp

                    <tr>
                        <td>{{ $att->user->name }}</td>
                        <td>{{ $start }}</td>
                        <td>{{ $end }}</td>
                        <td>{{ $break }}</td>
                        <td>{{ $total }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.show', $att->id) }}" class="detail-link">
                                詳細
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6">データがありません</td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

</div>
@endsection










