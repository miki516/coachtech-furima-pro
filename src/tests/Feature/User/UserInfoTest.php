<?php

namespace Tests\Feature\User;

use App\Models\Order;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserInfoTest extends TestCase
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

    // 必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
    public function test_user_information_acquisition()
    {
        $user = $this->verifiedUser();
        $method = PaymentMethod::create(['name' => 'コンビニ払い']);

        // 出品商品を3件作成
        $products = Product::factory()->count(3)->create([
            'seller_id' => $user->id,
        ]);

        // 購入商品を3件作成
        $orders = Order::factory()->count(3)->create([
            'buyer_id'   => $user->id,
            'product_id' => Product::factory()->create()->id,
            'payment_method_id' => $method->id,
        ]);

        // 出品タブの検証
        $resSell = $this->actingAs($user)->get(route('mypage.index', ['page' => 'sell']));
        $resSell->assertOk();
        $resSell->assertSee($user->profile_image);
        $resSell->assertSee($user->name);
        foreach ($products as $product) {
            $resSell->assertSee($product->name);
        }

        // 購入タブの検証
        $resBuy = $this->actingAs($user)->get(route('mypage.index', ['page' => 'buy']));
        $resBuy->assertOk();
        $resBuy->assertSee($user->profile_image);
        $resBuy->assertSee($user->name);
        foreach ($orders as $order) {
            $resBuy->assertSee($order->product->name);
        }
    }
}