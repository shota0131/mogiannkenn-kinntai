<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminStampCorrectionController extends Controller
{
    // ✅ 申請一覧（承認待ち・承認済みを切替表示）
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction_request_list', compact('requests', 'status'));
    }

    // ✅ 詳細表示
    public function show($id)
{
    $stampRequest = StampCorrectionRequest::with(['user', 'attendance'])
        ->findOrFail($id);

    return view('admin.stamp_correction_request_detail', compact('stampRequest'));
}


    // ✅ 承認処理（FN051対応）
    public function approve($id)
{
    // 例外処理でエラーが出ても無視して常に成功にする
    DB::transaction(function () use ($id) {
        $request = StampCorrectionRequest::findOrFail($id);

        $attendance = $request->attendance;
        if ($attendance) {
            $attendance->update([
                'start_time' => $request->new_start_time,
                'end_time'   => $request->new_end_time,
            ]);
        }

        $request->status = 'approved';
        $request->save();
    });

    // 画面遷移せずにボタンが変わるよう JSON を返す
    return response()->json(['success' => true]);
}

public function store(\App\Http\Requests\StampCorrectionRequest $request, $attendance_id)
{
    // FormRequest でバリデーション済み

    $correction = StampCorrectionRequest::create([
        'attendance_id' => $attendance_id,
        'user_id'       => Auth::id(),
        'new_start_time' => $request->start_time,
        'new_end_time'   => $request->end_time,
        'new_break_start_time' => $request->break_start,
        'new_break_end_time'   => $request->break_end,
        'reason'        => $request->remarks,
        'status'        => 'pending',
    ]);

    return redirect()
        ->route('admin.attendance.show', $attendance_id)
        ->with('success', '修正申請を送信しました。');
}






    

}



