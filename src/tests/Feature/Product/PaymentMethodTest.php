<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodTest extends TestCase
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

    // 小計画面で変更が反映される
    public function test_payment_method_selection_has_proper_mechanism()
    {
        $buyer = $this->verifiedUser();
        $product = Product::factory()->create();
        $method  = PaymentMethod::factory()->create();

        // 先に購入画面を確認
        $page = $this->actingAs($buyer)
            ->get(route('purchase.create', ['product' => $product->getKey()]));
        $page->assertOk();
        $page->assertSee('id="selected-method"', false);
        $page->assertSee('id="payment_method"', false);

        // 購入リクエストを送る
        $this->actingAs($buyer)
            ->followingRedirects()
            ->post(route('purchase.store', ['product' => $product->getKey()]),
            ['payment_method_id' => $method->id]
        );

        // DBに保存されていることを確認
        $this->assertDatabaseHas('orders', [
            'buyer_id'          => $buyer->id,
            'product_id'        => $product->id,
            'payment_method_id' => $method->id,
        ]);
    }
}
