<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証</title>
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>
    </header>

    <div class="verify-container">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="http://localhost:8025" target="_blank" class="verify-button">
            認証はこちらから
        </a>

        @if(session('status') == 'verification-link-sent')
            <p class="resend-success">新しい認証メールを送信しました。</P>
        @endif 

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="resend-link">
                認証メールを再送する
            </button>
        </form>
    </div>
</body>
</html>