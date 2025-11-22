<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** 会員登録後、認証メールが送信される */
    public function test_register_sends_verification_email()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => '太郎',
            'email' => 'verify@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'verify@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** 認証ボタンを押すと認証サイトに遷移する */
    public function test_verification_notice_shows_link()
    {
        $user = User::factory()->unverified()->create();

        $res = $this->actingAs($user)->get('/email/verify');

        $res->assertStatus(200);
        $res->assertSee('認証はこちらから');
    }

    /** 認証が完了するとプロフィール設定ページに遷移する */
    public function test_verified_user_redirects_to_products()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $res = $this->actingAs($user)->get($verificationUrl);

        $res->assertRedirect('/mypage/profile?from=register');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
