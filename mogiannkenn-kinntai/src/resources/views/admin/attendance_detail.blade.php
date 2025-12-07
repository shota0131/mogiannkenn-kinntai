@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">

    <h3 class="detail-title">勤怠詳細</h3>

    <div class="detail-card">

        <form id="attendance-form" method="POST"
            action="{{ route('admin.stamp_correction_request.store', ['attendance_id' => $attendance->id]) }}">
            @csrf

            <div class="detail-row">
                <label>名前</label>
                <input type="text" value="{{ $attendance->user->name }}" disabled class="input-text">
            </div>

            <div class="detail-row">
                <label>日付</label>
                <input type="text"
                       value="{{ \Carbon\Carbon::parse($attendance->date)->format('Y年 n月j日') }}"
                       disabled class="input-text">
            </div>

            <div class="detail-row">
                <label>出勤・退勤</label>
                <div class="flex-box">
                    <input type="time" name="start_time"
                        value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}"
                        class="input-time" {{ $attendance->status === 'pending' ? 'readonly' : '' }}>
                    
                    <span class="separator">〜</span>
                    
                    <input type="time" name="end_time"
                        value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}"
                        class="input-time" {{ $attendance->status === 'pending' ? 'readonly' : '' }}>
                </div>

                @error('end_time')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="detail-row">
                <label>休憩</label>
                <div class="flex-box">
                    <input type="time" name="break_start"
                        value="{{ old('break_start', $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '') }}"
                        class="input-time" {{ $attendance->status === 'pending' ? 'readonly' : '' }}>
                    
                    <span class="separator">〜</span>

                    <input type="time" name="break_end"
                        value="{{ old('break_end', $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '') }}"
                        class="input-time" {{ $attendance->status === 'pending' ? 'readonly' : '' }}>
                </div>

                @error('break_start')
                    <p class="error">{{ $message }}</p>
                @enderror
                @error('break_end')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="detail-row">
                <label>備考</label>
                <input type="text" name="remarks"
                    value="{{ old('remarks', $attendance->remarks) }}"
                    class="input-text" {{ $attendance->status === 'pending' ? 'readonly' : '' }}>
                
                @error('remarks')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </div>

    <div class="btn-area">
    @if ($hasPending)
        <p id="pending-message" class="pending-message">
            ・承認待ちのため修正はできません。
        </p>
    @else
        <button type="submit" form="attendance-form" class="update-btn">修正</button>
    @endif
    </div>

</div>
@endsection




















