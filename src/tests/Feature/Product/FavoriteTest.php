<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'postal_code'       => '123-4567',
            'address'           => '東京都テスト区1-2-3',
            'building'          => 'テストビル',
        ]);
    }

    /** いいねするとDBに登録され、カウントが増える（UIは星がアクティブ） */
    public function test_user_can_favorite_and_count_increases()
    {
        $user = $this->verifiedUser();
        $product = Product::factory()->create();

        $res = $this->actingAs($user)->post(route('products.favorite', $product->id));
        $res->assertStatus(302); // 前のページへリダイレクト

        // DBに登録されたか
        $this->assertDatabaseHas('favorites', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        // 詳細ページでアクティブアイコン＆カウント表示（1）を確認
        $show = $this->actingAs($user)->get(route('products.show', ['item_id' => $product->id]));
        $show->assertOk();
        $show->assertSee('images/icons/star-active.svg');
        $show->assertSee('icon-count');
        $show->assertSee('1');
    }

    /** 追加済みのアイコンは色が変わる（アクティブ表示になる） */
    public function test_icon_turns_active_after_favorite()
    {
        $user = $this->verifiedUser();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(route('products.favorite', $product->id));

        $show = $this->actingAs($user)->get(route('products.show', ['item_id' => $product->id]));
        $show->assertOk();
        $show->assertSee('images/icons/star-active.svg');
        $show->assertDontSee('images/icons/star-inactive.svg');
    }

    /** もう一度押すと解除され、カウントが減る（UIは星がインアクティブ） */
    public function test_user_can_unfavorite_and_count_decreases()
    {
        $user = $this->verifiedUser();
        $product = Product::factory()->create();

        // 事前にいいね済み状態を作る
        Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

        // もう一度押す＝トグルで解除
        $res = $this->actingAs($user)->post(route('products.favorite', $product->id));
        $res->assertStatus(302);

        // DBから消えていること
        $this->assertDatabaseMissing('favorites', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        // 詳細ページでインアクティブ表示＆カウント（0）を確認
        $show = $this->actingAs($user)->get(route('products.show', ['item_id' => $product->id]));
        $show->assertOk();
        $show->assertSee('images/icons/star-inactive.svg');
        $show->assertDontSee('images/icons/star-active.svg');
        $show->assertSee('icon-count');
        $show->assertSee('0');
    }
}
