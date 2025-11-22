<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingAddressTest extends TestCase
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
        ]);
    }

    // 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
    public function test_purchase_page_shows_profile_address()
    {
        $buyer = $this->verifiedUser();
        $product = Product::factory()->create();

        $page = $this->actingAs($buyer)
            ->get(route('purchase.create', $product->getKey()));
        $page->assertOk();

        // 画面にプロフィール住所が出ている
        $page->assertSeeText('123-4567');
        $page->assertSeeText('東京都テスト区1-2-3');
        $page->assertSeeText('テストビル');
    }

    // 購入した商品に送付先住所が紐づいて登録される
    public function test_order_saves_profile_address_when_purchasing()
    {
        $buyer = $this->verifiedUser();
        $product = Product::factory()->create();
        $method  = PaymentMethod::factory()->create();

        // 購入
        $res = $this->actingAs($buyer)
            ->followingRedirects()
            ->post(route('purchase.store', ['product' => $product->getKey()]), [
                'payment_method_id' => $method->id,
            ]);
        $res->assertOk();

        // DBに保存されていることを確認
        $this->assertDatabaseHas('orders', [
            'buyer_id'          => $buyer->id,
            'product_id'        => $product->id,
            'payment_method_id' => $method->id,
        ]);

        // shipping_address は結合文字列
        $saved = Order::where('buyer_id', $buyer->id)
            ->where('product_id', $product->id)
            ->value('shipping_address');

        $this->assertNotNull($saved);
        $this->assertStringContainsString('123-4567', $saved);
        $this->assertStringContainsString('東京都テスト区1-2-3', $saved);
        $this->assertStringContainsString('テストビル', $saved);
        $this->assertStringContainsString('〒', $saved);
    }
}
