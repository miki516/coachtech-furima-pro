<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'product_id',
        'payment_method_id',
        'shipping_address',
        'total_price',
        'status',
        'buyer_rating',
        'seller_rating',
        'buyer_reviewed',
        'seller_reviewed',
        'last_message_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /** 取引チャットのメッセージ一覧 */
    public function tradeMessages()
    {
        return $this->hasMany(TradeMessage::class);
    }

    /** 最新メッセージ（並べ替え用） */
    public function latestMessage()
    {
        return $this->hasOne(TradeMessage::class)->latestOfMany();
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function scopeRelatedToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)
            ->orWhereHas('product', function ($q2) use ($userId) {
                $q2->where('seller_id', $userId);
            });
        });
    }

    public function scopeTrading($query)
    {
        return $query->where('status', 'trading');
    }

    public function scopeNotReviewed($query)
    {
        return $query->where(function ($q) {
            $q->where('buyer_reviewed', false)
            ->orWhere('seller_reviewed', false);
        });
    }
}
