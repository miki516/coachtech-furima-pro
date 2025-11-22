<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    // 購入確認画面を表示
    public function create(Product $product)
    {
        // 自分の商品は買えない
        if ($product->seller_id === Auth::id()) {
            abort(403, '自分の商品は購入できません');
        }

        // 販売中でなければ購入できない
        if ($product->status !== 'selling') {
            abort(404, 'この商品は購入できません');
        }

        $user = auth()->user();

        // セッションに配送先があればそれを使う、なければユーザー住所を使う
        $shipping = [
            'postal_code' => session('shipping_postal_code', $user->postal_code),
            'address'     => session('shipping_address', $user->address),
            'building'    => session('shipping_building', $user->building),
        ];

        return view('purchase.create', compact('product', 'user', 'shipping'));
    }

    public function store(PurchaseRequest $request, Product $product)
    {
        $validated = $request->validated();
        $user = auth()->user();

        // セッションに配送先があればそれを使う、なければユーザー住所を使う
        $shippingAddress = "〒" .
            (session('shipping_postal_code', $user->postal_code)) . ' ' .
            (session('shipping_address', $user->address)) . ' ' .
            (session('shipping_building', $user->building));

        $order = Order::create([
            'buyer_id'          => Auth::id(),
            'product_id'        => $product->getKey(),
            'payment_method_id' => $validated['payment_method_id'],
            'shipping_address'  => $shippingAddress,
            'total_price'       => $product->price,
            'status'            => 'trading',
        ]);

        // 商品も即 SOLD にする（購入ボタン押した時点で売り切れ扱い）
        $product->update([
            'status' => 'sold',
        ]);

        // セッションの配送先をクリア
        session()->forget(['shipping_postal_code', 'shipping_address', 'shipping_building']);

        // テスト環境ではStripeをスキップして、すぐに成功ルートへ
        if (app()->environment('testing')) {
            return redirect()->route('purchase.success', ['order' => $order->id]);
        }

        // Stripe APIキーを設定
        Stripe::setApiKey(config('services.stripe.secret'));

        // Checkout セッションを作成（カード & コンビニ両方を許可）
        $session = Session::create([
            'payment_method_types' => ['card', 'konbini'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $product->name],
                    'unit_amount' => (int) $product->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', ['order' => $order->id]),
            'cancel_url'  => route('purchase.cancel', ['order' => $order->id]),
        ]);

        // Stripeの決済ページにリダイレクト
        return redirect($session->url);
    }

    // Stripe 決済成功後に呼ばれる
    public function success(Order $order)
    {
        // 注文した本人しかアクセスできないようにチェック
        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'この注文にはアクセスできません');
        }

        // 支払い完了したので取引中ステータスへ
        $order->update([
            'status' => 'trading',
        ]);

        // 商品を売却済みに変更
        $order->product->update(['status' => 'sold']);

        return redirect()
            ->route('products.show', $order->product_id)
            ->with('success', '決済が完了しました！');
    }

    // Stripe 決済キャンセル時に呼ばれる
    public function cancel(Order $order)
    {
        // 注文した本人しかアクセスできないようにチェック
        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'この注文にはアクセスできません');
        }

        return redirect()
            ->route('products.show', ['item_id' => $order->product->getRouteKey()])
            ->with('error', '決済がキャンセルされました');
    }

    // 住所編集画面を表示
    public function edit(Product $product)
    {
        $user = auth()->user();
        return view('purchase.address', compact('user', 'product'));
    }

    public function update(AddressRequest $request, Product $product)
    {
        // 住所をセッションに保存（注文が確定するまでの一時保存）
        session([
            'shipping_postal_code' => $request->postal_code,
            'shipping_address'     => $request->address,
            'shipping_building'    => $request->building,
        ]);

        return redirect()
            ->route('purchase.create', $product->id)
            ->with('success', '配送先を更新しました！');
    }
}