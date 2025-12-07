@extends('layouts.app')

@section('title', '勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endpush

@section('content')
<div class="attendance-detail-container">
    <h2 class="page-title">勤怠詳細</h2>

    <div class="detail-card">
        <form id="attendanceForm" action="{{ route('attendance.correction.store', ['id' => $attendance->id]) }}"
        method="POST">
            @csrf

            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

            <table class="detail-table">
                <tr>
                    <th>氏名</th>
                    <td>{{ Auth::user()->name }}</td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年m月d日') }}</td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time" name="start_time"
                               value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}"> 〜
                        <input type="time" name="end_time"
                               value="{{ old('end_time', \Carbon\Carbon::parse($attendance->end_time)->format('H:i')) }}">

                        @if($errors->has('start_time'))
                            <div class="error-message">{{ $errors->first('start_time') }}</div>
                        @endif
                        @if($errors->has('end_time'))
                            <div class="error-message">{{ $errors->first('end_time') }}</div>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>休憩</th>
                    <td>
                        <input type="time" name="break_start"
                               value="{{ old('break_start', \Carbon\Carbon::parse($attendance->break_start)->format('H:i')) }}"> 〜
                        <input type="time" name="break_end"
                               value="{{ old('break_end', \Carbon\Carbon::parse($attendance->break_end)->format('H:i')) }}">

                        @if($errors->has('break_start'))
                            <div class="error-message">{{ $errors->first('break_start') }}</div>
                        @endif
                        @if($errors->has('break_end'))
                            <div class="error-message">{{ $errors->first('break_end') }}</div>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <input type="text" name="remarks" class="remarks-input"
                               value="{{ old('remarks', $attendance->remarks) }}">

                        @if($errors->has('remarks'))
                            <div class="error-message">{{ $errors->first('remarks') }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <div class="button-area">
        @if(session('lock') || (isset($lock) && $lock))
            <p id="lockMessage" class="error-message">
                ※承認待ちのため修正はできません
            </p>
        @else
            <button type="button" class="update-btn" onclick="document.getElementById('attendanceForm').submit();">
                修正
            </button>
            <p id="lockMessage" class="error-message" style="display:none;">
                ※承認待ちのため修正はできません
            </p>
        @endif
    </div>

</div>

@endsection











