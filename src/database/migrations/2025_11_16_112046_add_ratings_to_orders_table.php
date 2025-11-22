<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 取引状態
            $table->string('status')->default('pending')->after('total_price');

            // 最新メッセージ時刻（並び順のため）
            $table->timestamp('last_message_at')->nullable()->after('status');

            // 評価（1〜5）
            $table->integer('buyer_rating')->nullable()->after('last_message_at');
            $table->integer('seller_rating')->nullable()->after('buyer_rating');

            // 評価済みフラグ
            $table->boolean('buyer_reviewed')->default(false)->after('seller_rating');
            $table->boolean('seller_reviewed')->default(false)->after('buyer_reviewed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'last_message_at',
                'buyer_rating',
                'seller_rating',
                'buyer_reviewed',
                'seller_reviewed',
            ]);
        });
    }
};
