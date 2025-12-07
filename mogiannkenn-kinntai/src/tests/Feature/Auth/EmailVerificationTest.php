<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後に認証メールが送信される()
    {
        Notification::fake();

        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post('/register', $userData);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
    }

    /** @test */
    public function メール認証誘導画面から認証リンクに遷移できる()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');

        // 認証リンクを生成してアクセス
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance'); // 認証後に勤怠登録画面にリダイレクト
    }

    /** @test */
    public function メール認証を完了すると勤怠登録画面に遷移する()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
