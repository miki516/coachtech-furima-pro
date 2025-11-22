<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductRegisterTest extends TestCase
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

    // 商品出品画面にて必要な情報が保存できる（カテゴリ、商品の状態、商品名、ブランド名、商品の説明、販売価格）
    public function test_product_can_be_created_with_valid_data()
    {
        $seller = $this->verifiedUser();

        Storage::fake('public');

        // カテゴリを準備
        $category = Category::factory()->create();

        // 出品データを用意
        $data = [
            'category_id' => [$category->id],
            'condition'   => '新品',
            'name'        => 'テスト商品',
            'brand'       => 'テストブランド',
            'description' => 'これはテスト用の商品説明です。',
            'price'       => 1000,
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
        ];

        // 出品処理を実行
        $res = $this->actingAs($seller)
            ->post(route('products.store'), $data);
        $res->assertRedirect();

        // DBに保存されていることを確認
        $this->assertDatabaseHas('products', [
            'seller_id'   => $seller->id,
            'condition'   => '新品',
            'name'        => 'テスト商品',
            'brand'       => 'テストブランド',
            'description' => 'これはテスト用の商品説明です。',
            'price'       => 1000,
        ]);

        // 中間テーブルの確認
        $this->assertDatabaseHas('category_product', [
            'product_id'  => Product::first()->id,
            'category_id' => $category->id,
        ]);
    }
}
