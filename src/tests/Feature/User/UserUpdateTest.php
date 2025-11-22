<?php

namespace Tests\Feature\User;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware();
    }

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'postal_code'       => '123-4567',
            'address'           => '東京都テスト区1-2-3',
            'building'          => 'テストビル',
            'profile_image' => 'profile/test.png',
        ]);
    }

    public function test_user_edit_page_displays_initial_values()
    {
        $user = $this->verifiedUser();

        // ログインしてプロフィール編集画面にアクセス
        $res = $this->actingAs($user)
            ->get(route('profile.edit'));
        $res->assertOk();

        // 初期値の確認
        $res->assertSee($user->profile_image);
        $res->assertSee($user->name);
        $res->assertSee($user->postal_code);
        $res->assertSee($user->address);
        $res->assertSee($user->building);
    }
}
