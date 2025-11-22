<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    /** メールが空のときエラーになる */
    public function test_email_is_required()
    {
        $res = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $res->assertRedirect('/login');
        $res->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** パスワードが空のときエラーになる */
    public function test_password_is_required()
    {
        $res = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $res->assertRedirect('/login');
        $res->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** 間違った情報を入力したときエラーになる */
    public function test_invalid_credentials_show_error()
    {
        $res = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpass',
        ]);

        $res->assertRedirect('/login');
        $res->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /** 正しい情報ならログインできる */
    public function test_valid_credentials_redirect_to_home()
    {
        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('password123'),
        ]);

        $res = $this->post('/login', [
            'email' => 'valid@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $res->assertRedirect('/?tab=mylist');
    }
}
