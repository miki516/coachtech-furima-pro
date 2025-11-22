<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    /** 「商品名」で部分一致検索ができる（一覧ページ） */
    public function test_can_search_products_by_name_partial_match()
    {
        $match = Product::factory()->create(['name' => 'Red Camera']);
        $nonMatch = Product::factory()->create(['name' => 'Blue Phone']);

        $res = $this->get('/?q=cam');

        $res->assertOk();
        $res->assertSee(route('products.show', ['item_id' => $match->id]));
        $res->assertDontSee(route('products.show', ['item_id' => $nonMatch->id]));
    }

    /** 検索状態がマイリストのタブリンクにも引き継がれる（リンク検証） */
    public function test_search_keyword_is_carried_over_to_mylist_link()
    {
        Product::factory()->create(['name' => 'Any Product']);

        $res = $this->get('/?q=camera');

        $res->assertOk();
        $res->assertSee('tab=mylist&q=camera');
    }

    /** マイリストでも検索キーワードが効く（いいね済みの中から部分一致で絞り込み） */
    public function test_mylist_respects_search_keyword_and_shows_only_liked_matches()
    {
        $user = User::factory()->create();
        $p1 = Product::factory()->create(['name' => 'Pocket Camera']);
        $p2 = Product::factory()->create(['name' => 'Gaming Laptop']);

        $user->favoriteProducts()->attach([$p1->id, $p2->id]);

        $res = $this->actingAs($user)->get('/?tab=mylist&q=cam');

        $res->assertOk();
        $res->assertSee(route('products.show', ['item_id' => $p1->id]));
        $res->assertDontSee(route('products.show', ['item_id' => $p2->id]));
    }
}
