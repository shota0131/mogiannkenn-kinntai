@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('content')
<div class="staff-list-container">
    <h2 class="page-title">スタッフ一覧</h2>

    <div class="staff-list-card">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>氏名</th>
                    <th>メールアドレス</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffs as $staff)
                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}" class="detail-btn">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
