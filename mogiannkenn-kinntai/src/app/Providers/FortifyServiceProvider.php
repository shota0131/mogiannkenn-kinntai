<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Actions\Fortify\CreateNewUser;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void 
    {
        $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);
    }

    public function boot(): void
    {
        // -----------------------------
        // 画面
        // -----------------------------
        Fortify::loginView(function () {
            return request()->is('admin/*')
                ? view('admin.login')
                : view('login');
        });

        Fortify::registerView(fn () => view('register'));
        Fortify::verifyEmailView(fn () => view('verify-email'));

        // -----------------------------
        // Request 差し替え
        // -----------------------------
        $this->app->bind(FortifyLoginRequest::class, function () {
            return request()->is('admin/*')
                ? new AdminLoginRequest()
                : new LoginRequest();
        });

        // -----------------------------
        // 認証処理
        // -----------------------------
        Fortify::authenticateUsing(function (Request $request) {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);
            $validator->validate();
        
            if ($request->is('admin/*')) {
                $admin = Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    $request->session()->regenerate();
                    session(['guard' => 'admin']);
                    return $admin;
                }
            } else {
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    $user = Auth::user();
                    if (!$user->hasVerifiedEmail()) {
                        Auth::logout();
                        throw ValidationException::withMessages([
                            'email' => 'メールアドレスが認証されていません',
                        ]);
                    }
                    $request->session()->regenerate();
                    return $user;
                }
            }
        
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        
            

            // 一般ユーザー
            App::setLocale('ja'); // 日本語固定
            $validator = Validator::make(
                $request->all(),
                (new LoginRequest())->rules(),
                (new LoginRequest())->messages()
            );
            $validator->validate();

            return Auth::attempt([
                'email' => $request->email,
                'password' => $request->password
            ]) ? Auth::user() : null;
        });

        // -----------------------------
        // リダイレクト設定
        // -----------------------------
        Fortify::redirects('login', function () {
            return session('guard') === 'admin'
                ? '/admin/attendance/list'
                : route('attendance.form');
        });

        Fortify::redirects('register', function () {
            return route('attendance.form'); // 初回登録後は打刻画面
        });
    }
}








