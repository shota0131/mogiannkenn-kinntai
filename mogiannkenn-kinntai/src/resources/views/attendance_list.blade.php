@extends('layouts.app')

@section('title', '勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endpush

@section('content')
<div class="attendance-list-container">
    <h1 class="page-title">勤怠一覧</h1>

    <div class="month-selector">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="btn-month">&lt; 前月</a>
        <span class="current-month">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('Y年n月') }}</span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="btn-month">翌月 &gt;</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>勤務時間</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                @php
                    $breakMinutes = $attendance->breaks->sum(function($b){
                        return ($b->start_time && $b->end_time)
                            ? \Carbon\Carbon::parse($b->end_time)->diffInMinutes(\Carbon\Carbon::parse($b->start_time))
                            : 0;
                    });

                    $workMinutes = ($attendance->start_time && $attendance->end_time)
                        ? \Carbon\Carbon::parse($attendance->end_time)->diffInMinutes(\Carbon\Carbon::parse($attendance->start_time)) - $breakMinutes
                        : 0;
                @endphp

                <tr class="clickable-row" onclick="window.location='{{ route('attendance.detail', $attendance->id) }}'">
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('n/d(D)') }}</td>
                    <td>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-' }}</td>
                    <td>{{ $breakMinutes ? floor($breakMinutes/60).'h'.($breakMinutes%60).'m' : '-' }}</td>
                    <td>{{ $workMinutes ? floor($workMinutes/60).'h'.($workMinutes%60).'m' : '-' }}</td>
                    <td><a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a></td>
                </tr>

            @empty
                <tr><td colspan="6" class="no-data">データがありません</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
