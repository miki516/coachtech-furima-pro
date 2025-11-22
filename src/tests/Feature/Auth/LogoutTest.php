<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** ログアウトするとログイン画面にリダイレクトされる */
    public function test_user_can_logout()
    {
        // ユーザーを作成 & ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // POST /logout を叩く
        $res = $this->post('/logout');

        // ログイン画面にリダイレクトされることを確認
        $res->assertRedirect('/login');

        // 認証が解除されていることを確認
        $this->assertGuest();
    }
}
