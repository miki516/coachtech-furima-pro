<?php

namespace App\Http\Controllers;

use App\Mail\TradeCompletedMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TradeReviewController extends Controller
{
    public function create(Request $request, Order $order)
    {
        $user = $request->user();

        // 自分が関係している取引か確認
        if ($order->buyer_id !== $user->id && $order->product->seller_id !== $user->id) {
            abort(403);
        }

        // 取引中以外は評価させない（必要なら条件調整）
        if (! in_array($order->status, ['trading', 'pending'], true)) {
            abort(404);
        }

        // 自分が buyer か seller か判定
        $role = $order->buyer_id === $user->id ? 'buyer' : 'seller';

        // すでに自分側は評価済みなら404にしておく（再評価禁止）
        if ($role === 'buyer' && $order->buyer_reviewed) {
            abort(404);
        }
        if ($role === 'seller' && $order->seller_reviewed) {
            abort(404);
        }

        // 相手ユーザー
        $targetUser = $role === 'buyer'
            ? $order->product->seller
            : $order->buyer;

        return view('trade.review', [
            'order'      => $order,
            'role'       => $role,
            'targetUser' => $targetUser,
        ]);
    }

    public function store(Request $request, Order $order)
    {
        $user = $request->user();

        // 関係者チェック
        if ($order->buyer_id !== $user->id && $order->product->seller_id !== $user->id) {
            abort(403);
        }

        if (! in_array($order->status, ['trading', 'pending'], true)) {
            abort(404);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $isBuyer = $order->buyer_id === $user->id;

        if ($isBuyer) {
            if ($order->buyer_reviewed) {
                abort(404);
            }
            $order->buyer_rating   = $data['rating'];
            $order->buyer_reviewed = true;
        } else {
            if ($order->seller_reviewed) {
                abort(404);
            }
            $order->seller_rating   = $data['rating'];
            $order->seller_reviewed = true;
        }

        // 両者評価完了したら completed にする
        if ($order->buyer_reviewed && $order->seller_reviewed) {
            $order->status = 'completed';
        }

        // 購入者が初めて評価したタイミングでメール送信
        if ($isBuyer) {
        $seller = $order->product->seller;

        if ($seller && $seller->email) {
            Mail::to($seller->email)->send(new TradeCompletedMail($order));
        }
        }

        $order->save();

        return redirect()
            ->route('products.index', ['page' => 'trade'])
            ->with('success', '評価を送信しました。');
    }
}
