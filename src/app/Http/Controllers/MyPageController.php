<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\TradeMessage;
use Illuminate\Http\Request;

class MyPageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $averageRating = $this->calculateAverageRating($user->id);
        $activePage    = $this->resolvePage($request->query('page'));
        $tradeUnread   = $this->countTradeUnread($user->id);
        $items         = $this->getItemsByPage($activePage, $user->id);

        return view('mypage.index', [
            'user'             => $user,
            'activePage'       => $activePage,
            'items'            => $items,
            'tradeUnreadCount' => $tradeUnread,
            'averageRating'    => $averageRating['average'],
            'ratingCount'      => $averageRating['count'],
        ]);
    }

    private function calculateAverageRating($userId)
    {
        $sellerQuery = Order::whereHas('product', fn($q) => 
            $q->where('seller_id', $userId)
        )->whereNotNull('buyer_rating');

        $buyerQuery = Order::where('buyer_id', $userId)
            ->whereNotNull('seller_rating');

        $sum   = (float) $sellerQuery->sum('buyer_rating') + (float) $buyerQuery->sum('seller_rating');
        $count = (int) $sellerQuery->count() + (int) $buyerQuery->count();

        return [
            'average' => $count ? $sum / $count : null,
            'count'   => $count,
        ];
    }

    private function resolvePage($page)
    {
        return in_array($page, ['sell', 'buy', 'trade'], true) ? $page : 'sell';
    }

    private function countTradeUnread($userId)
    {
        return TradeMessage::where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->whereHas('order', function ($q) use ($userId) {
                $q->relatedToUser($userId)
                  ->trading()
                  ->notReviewed();
            })
            ->count();
    }

    private function getItemsByPage($page, $userId)
    {
        return match ($page) {
            'sell'  => $this->getSellItems($userId),
            'buy'   => $this->getBuyItems($userId),
            'trade' => $this->getTradeItems($userId),
        };
    }

    private function getSellItems($userId)
    {
        return Product::with('categories')
            ->where('seller_id', $userId)
            ->latest()
            ->paginate(12, ['*'], 'p')
            ->withQueryString();
    }

    private function getBuyItems($userId)
    {
        $productIds = Order::where('buyer_id', $userId)->pluck('product_id');

        return Product::with('categories')
            ->whereIn('id', $productIds)
            ->latest()
            ->paginate(12, ['*'], 'p')
            ->withQueryString();
    }

    private function getTradeItems($userId)
    {
        return Order::with('product.categories')
            ->relatedToUser($userId)
            ->trading()
            ->notReviewed()
            ->withCount([
                'tradeMessages as unread_messages_count' => fn($q) =>
                    $q->where('sender_id', '!=', $userId)
                      ->where('is_read', false)
            ])
            ->orderByDesc('last_message_at')
            ->paginate(12, ['*'], 'p')
            ->withQueryString()
            ->through(function ($order) {
                $product = $order->product;
                $product->orderIdForTrade    = $order->id;
                $product->unreadMessagesCount = $order->unread_messages_count;
                return $product;
            });
    }
}
