<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;


class AdminAttendanceController extends Controller
{
    // 🔹 管理者：勤怠一覧
    public function index(Request $request)
{
    // 表示する日付（指定がなければ今日）
    $date = $request->input('date')
        ? Carbon::parse($request->input('date'))
        : Carbon::today();

    // 前日・翌日
    $prevDate = $date->copy()->subDay()->format('Y-m-d');
    $nextDate = $date->copy()->addDay()->format('Y-m-d');

    // 当日の勤怠一覧
    $attendances = Attendance::with('user')
        ->whereDate('date', $date->format('Y-m-d'))
        ->orderBy('user_id')
        ->get();

    return view('admin.attendance_list', compact(
        'attendances',
        'date',
        'prevDate',
        'nextDate'
    ));
}

    // 🔹 管理者：勤怠詳細
    public function show($id)
{
    $attendance = Attendance::with('user')->findOrFail($id);

    // 🔸 対象勤怠に対して「未承認の修正申請」があるか確認
    $hasPending = \App\Models\StampCorrectionRequest::where('attendance_id', $id)
        ->where('status', 'pending')
        ->exists();

    return view('admin.attendance_detail', [
        'attendance'  => $attendance,
        'hasPending'  => $hasPending,
    ]);
}

    

    // 勤怠データ更新
    public function update(AdminAttendanceUpdateRequest $request, $id)
{
    $attendance = Attendance::findOrFail($id);

    // バリデーション済みデータ
    $validated = $request->validated();

    // 勤怠データ更新（文字列のままでOK）
    $attendance->update([
        'start_time'  => $validated['start_time'],
        'end_time'    => $validated['end_time'],
        'break_start' => $validated['break_start'] ?? null,
        'break_end'   => $validated['break_end'] ?? null,
        'remarks'     => $validated['remarks'],
        'status'      => 'approved',
    ]);

    // 修正申請の作成
    StampCorrectionRequest::create([
        'attendance_id'   => $attendance->id,
        'user_id'         => $attendance->user_id,
        'new_start_time'  => $validated['start_time'],
        'new_end_time'    => $validated['end_time'],
        'new_break_start_time' => $validated['break_start'] ?? null,
        'new_break_end_time'   => $validated['break_end'] ?? null,
        'reason'          => '[管理者修正] ' . $validated['remarks'],
        'status'          => 'pending',
    ]);

    return redirect()
        ->route('admin.stamp_correction_request.list', ['status' => 'pending'])
        ->with('success', '修正内容を申請として登録しました');
}



    

    // 🔹 スタッフ別勤怠一覧
    public function showByStaff(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.attendance_staff', compact('staff', 'attendances', 'month'));
    }

    // 🔹 CSV出力
    public function exportCsv(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩開始', '休憩終了', '備考']);
            foreach ($attendances as $a) {
                fputcsv($handle, [
                    $a->date,
                    $a->start_time ?? '',
                    $a->end_time ?? '',
                    $a->break_start ?? '',
                    $a->break_end ?? '',
                    $a->remarks ?? '',
                ]);
            }
            fclose($handle);
        });

        $filename = "{$staff->name}_{$month}_勤怠一覧.csv";
        $response->headers->set('Content-Type', 'text/csv; charset=Shift-JIS');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

        return $response;
    }

    

}


