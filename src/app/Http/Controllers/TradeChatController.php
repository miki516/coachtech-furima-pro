<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TradeMessage;
use App\Http\Requests\StoreTradeMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TradeChatController extends Controller
{
    public function index(Request $request, Order $order)
    {
        $user = $request->user();

        // 自分が関係してる取引以外見られないように制限
        if ($order->buyer_id !== $user->id && $order->product->seller_id !== $user->id) {
            abort(403);
        }

        // 未読のメッセージを既読にする
        $order->tradeMessages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 全メッセージ表示
        $messages = $order->tradeMessages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        // サイドバー用（自分が関係する「取引中」の取引一覧）
        $sideTrades = Order::with('product')
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                ->orWhereHas('product', function ($q2) use ($user) {
                    $q2->where('seller_id', $user->id);
                });
            })
            // 評価が終わっていないもの
            ->where('status', 'trading')
            ->where(function ($q) {
                $q->where('buyer_reviewed', false)
                ->orWhere('seller_reviewed', false);
            })
            // 並び順
            ->orderByDesc('last_message_at')
            ->get();

        return view('trade.chat', [
            'order'      => $order,
            'messages'   => $messages,
            'sideTrades' => $sideTrades,
            'user'       => $user,
        ]);
    }

    public function store(StoreTradeMessageRequest $request, Order $order)
    {
        $user = $request->user();

        // 自分が関係してる取引以外見られないように制限
        if ($order->buyer_id !== $user->id && $order->product->seller_id !== $user->id) {
            abort(403);
        }

        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trade_images', 'public');
        }

        $message = TradeMessage::create([
            'order_id'   => $order->id,
            'sender_id'  => $user->id,
            'message'    => $data['message'],
            'image_path' => $imagePath,
            'is_read'    => false,
        ]);

        // 並べ替え用
        $order->update(['last_message_at' => now()]);

        return redirect()->to(
            route('trade.chat', ['order' => $order->id]) . '#message-' . $message->id
        );
    }


    public function update(Request $request, TradeMessage $message)
    {
        $user = $request->user();

        // 自分のメッセージ以外は編集不可
        if ($message->sender_id !== $user->id) {
            abort(403);
        }

        // delete_image もバリデーションに追加
        $data = $request->validate([
            'message'      => 'required|string|max:400',
            'delete_image' => 'nullable|boolean',
        ]);

        // 本文更新
        $message->message = $data['message'];

        // 画像削除フラグが立ってたらストレージ＆カラムを消す
        if ($request->boolean('delete_image') && $message->image_path) {
            Storage::disk('public')->delete($message->image_path);
            $message->image_path = null;
        }

        $message->save();

        return redirect()->to(
            route('trade.chat', ['order' => $message->order_id]) . '#message-' . $message->id
        )->with('success', 'メッセージを編集しました。');
    }

    public function destroy(Request $request, TradeMessage $message)
    {
        $user = $request->user();

        if ($message->sender_id !== $user->id) {
            abort(403);
        }

        $orderId = $message->order_id;

        // 削除前に「どこに戻るか」を決めておく
        // 基本は「ひとつ上のメッセージ」、なければ「ひとつ下のメッセージ」
        $targetMessageId = null;

        // ひとつ上（同じ注文内・自分以外・作成日時が前のものを新しい順で1件）
        $prev = TradeMessage::where('order_id', $orderId)
            ->where('id', '!=', $message->id)
            ->where('created_at', '<', $message->created_at)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($prev) {
            $targetMessageId = $prev->id;
        } else {
            // 上が無いときは、ひとつ下
            $next = TradeMessage::where('order_id', $orderId)
                ->where('id', '!=', $message->id)
                ->where('created_at', '>', $message->created_at)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($next) {
                $targetMessageId = $next->id;
            }
        }

        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }

        $message->delete();

        // アンカー文字列を組み立て
        $hash = $targetMessageId
            ? '#message-' . $targetMessageId
            : '#trade-form';

        return redirect()->to(
            route('trade.chat', ['order' => $orderId]) . $hash
        )->with('success', 'メッセージを削除しました。');
    }
}