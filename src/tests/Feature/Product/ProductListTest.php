<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    /** 全商品が一覧表示される */
    public function test_all_products_are_listed()
    {
        $products = Product::factory()->count(10)->create();

        $res = $this->get('/');

        $res->assertOk();
        foreach ($products as $p) {
            $res->assertSee(route('products.show', ['item_id' => $p->id]));
        }
    }

    /** 購入済み商品は「Sold」と表示される */
    public function test_sold_products_show_sold_label()
    {
        $sold = Product::factory()->create(['status' => 'sold']);

        $res = $this->get('/');

        $res->assertOk();
        $res->assertSee(route('products.show', ['item_id' => $sold->id]));
        $res->assertSee('Sold');
    }

    /** 自分が出品した商品は表示されない */
    public function test_user_cannot_see_own_products()
    {
        $user = User::factory()->create();
        $mine = Product::factory()->create(['seller_id' => $user->id]);
        $others = Product::factory()->count(2)->create();

        $res = $this->actingAs($user)->get('/');

        $res->assertOk();
        $res->assertDontSee(route('products.show', ['item_id' => $mine->id]));
        foreach ($others as $p) {
            $res->assertSee(route('products.show', ['item_id' => $p->id]));
        }
    }
}
