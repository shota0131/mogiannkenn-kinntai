@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_request_detail.css') }}">
@endsection

@section('content')
<div class="correction-container">
    <h2 class="page-title">勤怠詳細</h2>

    <div class="correction-card">

        {{-- 勤怠テーブル --}}
        <table class="correction-table">
            <tr>
                <th>氏名</th>
                <td>{{ $stampRequest->user->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($stampRequest->attendance->date)->format('Y年m月d日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ $stampRequest->attendance->start_time ?? '--:--' }} 〜
                    {{ $stampRequest->attendance->end_time ?? '--:--' }}
                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td>
                    {{ $stampRequest->attendance->break_start ?? '--:--' }} 〜
                    {{ $stampRequest->attendance->break_end ?? '--:--' }}
                </td>
            </tr>

            <tr>
                <th>休憩2</th>
                <td>
                    {{ $stampRequest->attendance->break2_start ?? '--:--' }} 〜
                    {{ $stampRequest->attendance->break2_end ?? '--:--' }}
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>{{ $stampRequest->reason ?: '---' }}</td>
            </tr>
        </table>
    </div>

    {{-- 承認ボタン（カード外・右下固定） --}}
    <div class="approval-area">
        <button 
            id="approveBtn" 
            class="approve-btn {{ $stampRequest->status === 'approved' ? 'disabled' : '' }}" 
            {{ $stampRequest->status === 'approved' ? 'disabled' : '' }}
            data-request-id="{{ $stampRequest->id }}">
            {{ $stampRequest->status === 'approved' ? '承認済み' : '承認' }}
        </button>
    </div>

</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveBtn = document.getElementById('approveBtn');
    if (!approveBtn || approveBtn.disabled) return;

    approveBtn.addEventListener('click', function() {
        const requestId = this.dataset.requestId;

        fetch(`/admin/stamp_correction_request/${requestId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            // 成功ならボタンを灰色に変える
            approveBtn.classList.add('disabled');
            approveBtn.disabled = true;
            approveBtn.textContent = '承認済み';
        })
        .catch(err => {
            // 万一エラーが出てもボタンを灰色化
            console.error(err);
            approveBtn.classList.add('disabled');
            approveBtn.disabled = true;
            approveBtn.textContent = '承認済み';
        });
    });
});
</script>
@endpush




