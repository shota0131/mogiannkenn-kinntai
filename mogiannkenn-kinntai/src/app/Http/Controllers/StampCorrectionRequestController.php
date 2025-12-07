<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    /**
     * 修正申請登録
     */
    public function store(Request $request, $attendanceId)
{
    $validator = \Validator::make($request->all(), [
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'break_start' => 'nullable|date_format:H:i',
        'break_end' => 'nullable|date_format:H:i|after:break_start',
        'remarks' => 'required|string|max:255',
    ], [
        'start_time.required' => '出勤時間を入力してください。',
        'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です。',
        'break_end.after' => '休憩時間もしくは退勤時間が不適切な値です。',
        'remarks.required' => '備考を記入してください。',
    ]);

    // バリデーションエラー → JSON で返す
    if ($validator->fails()) {
        return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
    }

    // データ登録
    StampCorrectionRequest::create([
        'attendance_id' => $attendanceId,
        'user_id' => Auth::id(),
        'new_start_time' => $request->start_time,
        'new_end_time' => $request->end_time,
        'new_break_start_time' => $request->break_start,
        'new_break_end_time' => $request->break_end,
        'reason' => $request->remarks,
        'status' => 'pending',
    ]);

    return redirect()
    ->route('attendance.detail', ['id' => $attendanceId])
    ->with('success', '修正申請を送信しました')
    ->with('lock', true);
}

    /**
     * 申請一覧（承認待ち／承認済み）
     */
    public function index(Request $request)
{
    // ?status=pending / ?status=approved を受け取る。指定がなければ pending。
    $status = $request->query('status', 'pending');

    // ログインユーザーの申請を status で絞る
    $requests = StampCorrectionRequest::where('user_id', Auth::id())
        ->where('status', $status)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('stamp_correction_request_list', compact('requests', 'status'));
}


    /**
     * 詳細（勤怠詳細を再利用）
     */
    public function show($id)
    {
        $request = StampCorrectionRequest::with('attendance')->findOrFail($id);

        // 自分以外の申請なら403
        if ($request->user_id !== Auth::id()) {
            abort(403);
        }

        // 承認待ちなら修正不可
        $lock = $request->status === 'pending';

        return view('attendance_detail', [
            'attendance' => $request->attendance,
            'lock' => $lock,
        ]);
    }

    public function userList()
{
    $requests = StampCorrectionRequest::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

    return view('stamp_correction.user_list', compact('requests'));
}

}

