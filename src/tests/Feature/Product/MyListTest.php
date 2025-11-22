<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    /** いいねした商品だけが表示される */
    public function test_only_liked_products_are_listed()
    {
        $user = User::factory()->create();

        // 名前が他と衝突しないように固定
        $p1 = Product::factory()->create(['name' => 'PROD_A_' . uniqid()]);
        $p2 = Product::factory()->create(['name' => 'PROD_B_' . uniqid()]);

        // 片方だけお気に入り
        $user->favoriteProducts()->attach($p1->id);

        $res = $this->actingAs($user)->get('/?tab=mylist');

        $res->assertStatus(200);
        $res->assertSeeText($p1->name);
        $res->assertDontSeeText($p2->name);
    }

    /** 購入済み商品は「Sold」と表示される */
    public function test_sold_products_show_sold_label_in_mylist()
    {
        $user = User::factory()->create();
        $sold = Product::factory()->create(['status' => 'sold']);

        $user->favoriteProducts()->attach($sold->id);

        $res = $this->actingAs($user)->get('/?tab=mylist');

        $res->assertStatus(200);
        $res->assertSee($sold->name);
        $res->assertSee('Sold');
    }

    /** 未認証の場合は何も表示されない */
    public function test_guest_cannot_see_mylist()
    {
        $res = $this->get('/?tab=mylist');

        $res->assertStatus(200);
        $res->assertDontSee('product-card'); // 空表示を期待
    }
}
