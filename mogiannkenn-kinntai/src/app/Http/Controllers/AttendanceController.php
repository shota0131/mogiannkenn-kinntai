<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\StampCorrectionRequest;
use App\Models\StampCorrectionRequest as SCR;


class AttendanceController extends Controller
{
    /**
     * 出勤画面表示
     */
    public function showAttendanceForm()
{
    $user = Auth::user();
    $today = Carbon::today();

    $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('date', $today)
        ->first();

    
    if (!$attendance) {
        $status = 'before_work'; // 出勤前
    } elseif ($attendance->start_time && !$attendance->end_time) {
        // 出勤済みで退勤していない場合
        if ($attendance->breaks()->whereNull('end_time')->exists()) {
            $status = 'on_break'; // 休憩中
        } else {
            $status = 'working'; // 出勤中
        }
    } elseif ($attendance->end_time) {
        $status = 'after_work'; // 退勤済み
    } else {
        $status = 'before_work'; // 念のため
    }

    $statusLabel = [
        'before_work' => '勤務外',
        'working' => '出勤中',
        'on_break' => '休憩中',
        'after_work' => '退勤済',
    ][$status];

    return view('attendance', compact('status', 'statusLabel'));
}


    /**
     * 出勤開始
     */
    public function start()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $already = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        if ($already) {
            return redirect()->route('attendance.form')->with('error', '本日はすでに出勤しています。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => Carbon::now(),
        ]);

        return redirect()->route('attendance.form');
    }

    /**
     * 休憩開始
     */
    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && !$attendance->end_time && !$attendance->breaks()->whereNull('end_time')->exists()) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance.form');
    }

    /**
     * 休憩終了
     */
    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance) {
            $break = $attendance->breaks()->whereNull('end_time')->latest()->first();
            if ($break) {
                $break->update(['end_time' => Carbon::now()]);
            }
        }

        return redirect()->route('attendance.form');
    }

    /**
     * 退勤
     */
    public function end()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && !$attendance->end_time && !$attendance->breaks()->whereNull('end_time')->exists()) {
            $attendance->update(['end_time' => Carbon::now()]);
            return redirect()->route('attendance.form')->with('message', 'お疲れ様でした！');
        }

        return redirect()->route('attendance.form')->with('error', '退勤できません。');
    }

    /**
     * 勤怠一覧
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->where('date', 'like', "{$month}%")
            ->orderBy('date', 'asc')
            ->get();

        $current = Carbon::createFromFormat('Y-m', $month);
        $prevMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');

        return view('attendance_list', compact('attendances', 'month', 'prevMonth', 'nextMonth'));
    }

    public function show($id)
{
    $userId = Auth::id();

    // ログインユーザーのデータのみ取得（他ユーザーのデータは見れないように）
    $attendance = Attendance::with('breaks')
        ->where('user_id', $userId)
        ->findOrFail($id);

    // 勤務時間計算
    $breakMinutes = $attendance->breaks->sum(function ($b) {
        return ($b->start_time && $b->end_time)
            ? Carbon::parse($b->end_time)->diffInMinutes(Carbon::parse($b->start_time))
            : 0;
    });

    $workMinutes = ($attendance->start_time && $attendance->end_time)
        ? Carbon::parse($attendance->end_time)->diffInMinutes(Carbon::parse($attendance->start_time)) - $breakMinutes
        : 0;

    return view('attendance_detail', compact('attendance', 'breakMinutes', 'workMinutes'));
}

public function update(StampCorrectionRequest $request, $id)
{
    $attendance = Attendance::where('user_id', Auth::id())->findOrFail($id);

    SCR::create([
        'user_id'        => Auth::id(),
        'attendance_id'  => $attendance->id,
        'start_time'     => $request->start_time,
        'end_time'       => $request->end_time,
        'break_start'    => $request->break_start,
        'break_end'      => $request->break_end,
        'reason'         => $request->remarks,
        'status'         => 'pending',
    ]);

    return redirect()
        ->route('attendance.show', ['id' => $attendance->id])
        ->with('success', '修正申請を送信しました。（承認待ち）');
}


    /**
     * 共通：当日の勤怠取得
     */
    private function getTodayAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();
    }
}



