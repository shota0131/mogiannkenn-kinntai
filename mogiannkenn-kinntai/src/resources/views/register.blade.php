<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>
    </header>

    <main class="register-container">
        <h1 class="register-title">会員登録</h1>

        <form method="POST" action="{{ route('register') }}" class="register-form">
            @csrf 

            <div class="form-group">
                <label for="name">名前</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}">
                @error('name')
                    {{ $message }}
                @enderror
            </div>

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                    {{ $message }}
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password">
                @error('password')
                    {{ $message }}
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">確認用パスワード</label>
                <input type="password" name="password_confirmation" id="password_confirmation">
            </div>

            <button type="submit" class="register-button">登録する</button>

            <p class="register-link">
                <a href="{{ route('login') }}">ログインはこちら</a>
            </p>
        </form>
    </main>
</body>
</html>