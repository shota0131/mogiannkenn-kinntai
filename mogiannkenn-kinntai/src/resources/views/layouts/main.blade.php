
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>

    {{-- 個別CSS読み込み用 --}}
    @yield('css')

    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>

    {{-- ★ 共通ヘッダー（必要なら別ファイル化推奨） --}}
    <header class="header">
        <div class="header-left">
            <img src="{{ asset('images/logo.svg') }}" class="header-logo">
        </div>

        <nav class="header-nav">
            <a href="{{ route('attendance.list') }}">今月の出勤一覧</a>
            <a href="{{ route('stamp_correction.list') }}">申請一覧</a>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </nav>
    </header>

    {{-- ★ ページごとの中身 --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- 個別JS --}}
    @yield('js')
</body>
</html>