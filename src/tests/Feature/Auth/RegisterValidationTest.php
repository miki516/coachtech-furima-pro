<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    /** 名前が空のときエラーになる */
    public function test_name_is_required()
    {
        $res = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** メールが空のときエラーになる */
    public function test_email_is_required()
    {
        $res = $this->from('/register')->post('/register', [
            'name' => '太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** パスワードが空のときエラーになる */
    public function test_password_is_required()
    {
        $res = $this->from('/register')->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** パスワードが7文字以下のときエラーになる */
    public function test_password_must_be_at_least_8_chars()
    {
        $res = $this->from('/register')->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => 'short7',
            'password_confirmation' => 'short7',
        ]);

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** 確認用と不一致のときエラーになる */
    public function test_password_must_match_confirmation()
    {
        $res = $this->from('/register')->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $res->assertRedirect('/register');
        $res->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /** 正しい入力なら登録され、メール認証画面に遷移する */
    public function test_register_success_redirects_to_profile()
    {
        $res = $this->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // DBにユーザーが作られているか
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 遷移先はメール認証案内ページ
        $res->assertRedirect('/email/verify');
    }
}
