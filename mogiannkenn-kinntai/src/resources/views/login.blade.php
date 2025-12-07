<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>
    </header>

    <main class="login-container">
        <h1 class="login-title">ログイン</h1>

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf 
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                    {{ $message }}
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password" value="{{ old('password') }}">
                @error('password')
                    {{ $message }}
                @enderror
            </div>

            <button type="submit" class="login-button">ログインする</button>

            <p class="register-link">
                <a href="/register">会員登録はこちら</a>
            </p>
        </form>
    </main>
</body>
</html>