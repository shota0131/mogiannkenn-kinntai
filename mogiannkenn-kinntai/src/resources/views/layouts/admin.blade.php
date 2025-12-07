<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>@yield('title')｜管理者</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    @yield('css')
</head>
<body>
    <header class="admin-header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>
        <nav class="admin-nav">
            <ul>
                <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                <li><a href="{{ route('admin.stamp_correction_request.list') }}">申請一覧</a></li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="logout-btn">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <main class="admin-main">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>