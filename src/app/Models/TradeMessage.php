<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sender_id',
        'message',
        'image_path',
        'is_read',
    ];

    /** どの注文に紐づくか */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** 送信者（ユーザー） */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
