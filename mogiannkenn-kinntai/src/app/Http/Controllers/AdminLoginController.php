<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    // ✅ 管理者ログインフォーム表示
    public function showLoginForm()
    {
        return view('admin.login');
    }

    // ✅ 管理者ログイン処理
    public function login(AdminLoginRequest $request)
{
    $admin = Admin::where('email', $request->email)->first();
    if ($admin && Hash::check($request->password, $admin->password)) {
        Auth::guard('admin')->login($admin);
        return redirect()->intended('/admin/attendance/list');
    }
    return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
}

    // ✅ ログアウト処理（管理者専用）
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}

