<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="logo">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </div>
            <nav class="nav">
                <a href="/attendance">勤怠</a>
                <a href="/attendance/list">勤怠一覧</a>
                <a href="/stamp_correction_request/list">申請</a>
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <a href="#" class="logout-link" onclick="document.getElementById('logoutForm').submit(); return false;">
                        ログアウト
                    </a>
                </form>
            </nav>
        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>
