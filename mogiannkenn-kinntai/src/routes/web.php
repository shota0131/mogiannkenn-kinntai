<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminStampCorrectionController;
use App\Http\Controllers\AdminLoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| 初期画面：ログインへリダイレクト（一般ユーザー）
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| メール認証（auth のみ / verified は不要）
|--------------------------------------------------------------------------
*/

// 認証要求ページ（PG01 〜 PG02 の後）
Route::get('/email/verify', function () {
    return view('verify-email');
})->middleware(['auth'])->name('verification.notice');

// メール内リンク（署名付き）
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了
    return redirect('/attendance'); // 認証後の遷移先（PG03）
})->middleware(['auth', 'signed'])->name('verification.verify');

// 再送処理
Route::post('/email/verification-notification', function () {
    request()->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])
  ->name('verification.send');

/*
|--------------------------------------------------------------------------
| 一般ユーザー機能（auth + verified）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // PG03 勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'showAttendanceForm'])
        ->name('attendance.form');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])
        ->name('attendance.end');
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.breakStart');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.breakEnd');

    // PG04 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])
        ->name('attendance.list');

    // PG05 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.detail');

    // PG06 申請一覧（一般ユーザー）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction.list');

    // 詳細（PG06 の遷移先）
    Route::get('/stamp_correction_request/{id}', [StampCorrectionRequestController::class, 'show'])
        ->name('stamp_correction.show');

    // 修正申請作成
    Route::post('/attendance/correction/{id}', [StampCorrectionRequestController::class, 'store'])
        ->name('attendance.correction.store');

    // 勤怠更新（仕様書にないが既存仕様）
    Route::post('/attendance/update/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');

    // ログアウト
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

/*
|--------------------------------------------------------------------------
| 管理者ログイン（PG07）
|--------------------------------------------------------------------------
*/
// web.php
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

/*
|--------------------------------------------------------------------------
| 管理者機能（auth:admin）
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {

    // PG08 管理者 勤怠一覧
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');

    // PG09 管理者 勤怠詳細
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.show');

    // 勤怠更新
    Route::post('/attendance/update/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    // PG11 スタッフ別勤怠一覧
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showByStaff'])
        ->name('admin.attendance.staff');

    // CSV エクスポート
    Route::get('/attendance/staff/{id}/export-csv', [AdminAttendanceController::class, 'exportCsv'])
        ->name('admin.attendance.exportCsv');

    // PG10 スタッフ一覧
    Route::get('/staff/list', [AdminStaffController::class, 'index'])
        ->name('admin.staff.list');

    // PG12・PG13 修正申請系（管理者）
    Route::get('/stamp_correction_request/list', [AdminStampCorrectionController::class, 'index'])
        ->name('admin.stamp_correction_request.list');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', 
        [AdminStampCorrectionController::class, 'show'])
        ->name('admin.stamp_correction_request.show');

    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', 
        [AdminStampCorrectionController::class, 'approve'])
        ->name('admin.stamp_correction_request.approve');

    Route::post('/stamp_correction_request/create/{attendance_id}', 
        [AdminStampCorrectionController::class, 'store'])
        ->name('admin.stamp_correction_request.store');
});







