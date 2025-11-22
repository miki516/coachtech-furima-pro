<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    /** 商品詳細ページに必要な情報が表示される */
    public function test_product_detail_shows_all_required_info()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $user->id,
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'price' => 1234,
            'description' => 'これは説明です',
            'condition' => '良好',
            'status' => 'selling',
            'image_path' => 'products/sample.jpg',
        ]);

        // コメント2件追加
        Comment::factory()->count(2)->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'content' => 'コメントです',
        ]);

        $res = $this->get("/item/{$product->id}");

        $res->assertOk();
        $res->assertSee($product->name);
        $res->assertSee($product->brand);
        $res->assertSee('¥');
        $res->assertSee(number_format($product->price));
        $res->assertSee($product->description);
        $res->assertSee($product->condition);

        // コメント数・内容が表示される
        $res->assertSee('コメント');
        $res->assertSee('コメントです');
        $res->assertSee($user->name);
    }

    /** 複数カテゴリが表示される */
    public function test_product_detail_shows_multiple_categories()
    {
        $product = Product::factory()->create();

        $categories = Category::factory()->count(2)->create();
        $product->categories()->attach($categories->pluck('id'));

        $res = $this->get("/item/{$product->id}");

        $res->assertOk();
        foreach ($categories as $c) {
            $res->assertSee($c->name);
        }
    }
}
