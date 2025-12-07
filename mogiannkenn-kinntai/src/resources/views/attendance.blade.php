@extends('layouts.app')

@section('title', '出勤登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')
<div class="attendance-container">
    {{-- ✅ メッセージ表示 --}}
    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- 状態バッジ --}}
    <div class="status-badge {{ $status }}">
        {{ $statusLabel }}
    </div>

    {{-- 日付と時刻 --}}
    <div class="date">{{ now()->format('Y年n月j日（D）') }}</div>
    <div class="time">{{ now()->format('H:i') }}</div>

    {{-- 状態別ボタン --}}
    @switch($status)
        @case('before_work')
            <form method="POST" action="{{ route('attendance.start') }}">
                @csrf
                <button type="submit" class="attendance-button black-btn">出勤</button>
            </form>
        @break

        @case('working')
            <div class="button-group">
                <form method="POST" action="{{ route('attendance.breakStart') }}">
                    @csrf
                    <button type="submit" class="attendance-button white-btn">休憩入</button>
                </form>
                <form method="POST" action="{{ route('attendance.end') }}">
                    @csrf
                    <button type="submit" class="attendance-button black-btn">退勤</button>
                </form>
            </div>
        @break

        @case('on_break')
            <form method="POST" action="{{ route('attendance.breakEnd') }}">
                @csrf
                <button type="submit" class="attendance-button white-btn">休憩戻</button>
            </form>
        @break

        @case('after_work')
            <div class="thanks-message">
                <p>お疲れ様でした！</p>
            </div>
        @break
    @endswitch
</div>
@endsection


