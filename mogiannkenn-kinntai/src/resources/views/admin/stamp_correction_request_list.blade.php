@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_request_list.css') }}">
@endsection

@section('content')
<div class="admin-request-container">
    <h2 class="page-title">申請一覧</h2>

    <div class="tab-area">
        <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'pending']) }}"
           class="tab {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'approved']) }}"
           class="tab {{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <div class="table-card">
        <table class="request-table">
            <thead>
                <tr>
                    <th>申請者</th>
                    <th>勤務日</th>
                    <th>出勤時刻</th>
                    <th>退勤時刻</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>{{ $req->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ $req->new_start_time ?? '--:--' }}</td>
                        <td>{{ $req->new_end_time ?? '--:--' }}</td>
                        <td class="reason">{{ Str::limit($req->reason, 20, '...') }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->created_at)->format('Y/m/d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.stamp_correction_request.show', $req->id) }}" class="detail-btn">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="no-data">申請データがありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
