<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase
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

    // 「購入する」ボタンを押下すると購入が完了する
    public function test_purchase_complete()
    {
        $buyer = $this->verifiedUser();
        $product = Product::factory()->create();
        $method  = PaymentMethod::factory()->create();
        $this->assertNotSame($buyer->id, $product->seller_id); // 自分買い防止

        $res = $this->actingAs($buyer)
            ->followingRedirects()
            ->post(route('purchase.store', ['product' => $product->getKey()]), [
                'payment_method_id' => $method->id
            ]);

        $res->assertOk();

        // 購入できたことをDBで確認
        $this->assertDatabaseHas('orders',[
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
            'payment_method_id' => $method->id,
        ]);

        // ステータス更新を確認
        $this->assertSame('sold', $product->fresh()->status);
    }

    // 購入した商品は商品一覧画面にて「sold」と表示される
    public function test_purchase_item_displays_sold_on_index()
    {
        $buyer = $this->verifiedUser();
        $product = Product::factory()->create();
        $method  = PaymentMethod::factory()->create();
        $this->assertNotSame($buyer->id, $product->seller_id); // 自分買い防止

        $this->actingAs($buyer)
            ->followingRedirects()
            ->post(route('purchase.store', ['product' => $product->getKey()]), [
                'payment_method_id' => $method->id
            ]);

        $res = $this->get(route('products.index'));
        $res->assertSeeText('Sold');
    }

    // 「プロフィール/購入した商品一覧」に追加されている
    public function test_purchase_item_shows_in_profile_purchase_list()
    {
        $buyer = $this->verifiedUser();
        $product = Product::factory()->create();
        $method = PaymentMethod::factory()->create();
        $this->assertNotSame($buyer->id, $product->seller_id);

        $this->actingAs($buyer)
            ->followingRedirects()
            ->post(route('purchase.store', ['product' => $product->getKey()]), [
                'payment_method_id' => $method->id,
            ]);

        $res = $this->get(route('mypage.index', ['page' => 'buy']));
        $res->assertOk();
        $res->assertSeeText($product->name);
    }
}
