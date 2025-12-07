@extends('layouts.app')

@section('title', '申請一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
@endpush

@section('content')
<div class="request-container">

    <h2 class="page-title">申請一覧</h2>

    {{-- タブ --}}
    <div class="tab-menu">
        <a href="{{ route('stamp_correction.list', ['status' => 'pending']) }}"
           class="tab-item {{ $status === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>

        <a href="{{ route('stamp_correction.list', ['status' => 'approved']) }}"
           class="tab-item {{ $status === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <div class="tab-underline"></div>

    {{-- テーブルカード --}}
    <div class="table-card">
        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>
                            @if($req->status === 'pending')
                                承認待ち
                            @else
                                承認済み
                            @endif
                        </td>

                        <td>{{ $req->user->name }}</td>
                        <td>{{ $req->attendance->date }}</td>
                        <td>{{ Str::limit($req->reason, 20) }}</td>
                        <td>{{ $req->created_at->format('Y/m/d') }}</td>

                        <td>
                            <a href="{{ route('stamp_correction.show', $req->id) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">申請データがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection





