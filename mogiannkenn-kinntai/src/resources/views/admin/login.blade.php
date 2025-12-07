<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>
    </header>

    <main class="login-container">
        <h1 class="login-title">管理者ログイン</h1>

        
        <form method="POST" action="{{ route('admin.login.submit') }}" class="login-form">
            @csrf 
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password">
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="login-button">管理者ログインする</button>
        </form>
    </main>
</body>
</html>
