<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TradeChatController;
use App\Http\Controllers\TradeReviewController;


// ==============================
// メール認証関連
// ==============================
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn () => view('auth.verify-email'))
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('profile.edit', ['from' => 'register']);
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

// ==============================
// 認証必須ルート（プロフィール完了済）
// ==============================
Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    // 出品
    Route::get('/sell', [ProductController::class, 'create'])->name('products.create');
    Route::post('/sell', [ProductController::class, 'store'])->name('products.store');

    // マイページ
    Route::get('/mypage', [MyPageController::class, 'index'])->name('mypage.index');

    // 購入
    Route::get('/purchase/{product}', [PurchaseController::class, 'create'])->name('purchase.create');
    Route::post('/purchase/{product}', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::get('/purchase/address/{product}', [PurchaseController::class, 'edit'])->name('purchase.address.edit');
    Route::post('/purchase/address/{product}', [PurchaseController::class, 'update'])->name('purchase.address.update');

    // Stripe 決済リダイレクト
    Route::get('/purchase/success/{order}', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/cancel/{order}', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

    // コメント
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');

    // お気に入り
    Route::post('/products/{id}/favorite', [ProductController::class, 'toggleFavorite'])
        ->name('products.favorite');

    // 取引チャット画面
    Route::get('/trade/{order}', [TradeChatController::class, 'index'])
        ->name('trade.chat')
        ->middleware(['auth', 'verified', 'profile.complete']);

    // メッセージ送信
    Route::post('/trade/{order}/message', [TradeChatController::class, 'store'])
        ->name('trade.message.store');

    // メッセージ編集
    Route::patch('/trade/message/{message}', [TradeChatController::class, 'update'])
        ->name('trade.message.update');

    // メッセージ削除
    Route::delete('/trade/message/{message}', [TradeChatController::class, 'destroy'])
        ->name('trade.message.destroy');

    // 取引評価
    Route::get('/trade/{order}/review', [TradeReviewController::class, 'create'])
        ->name('trade.review.create');
    Route::post('/trade/{order}/review', [TradeReviewController::class, 'store'])
        ->name('trade.review.store');
});

Route::post('/comments', [CommentController::class, 'store'])
    ->name('comments.store')
    ->middleware(['auth','verified','profile.complete']);

// ==============================
// プロフィール編集（プロフィール未完了でもOK）
// ==============================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// ==============================
// 公開ルート（認証不要）
// ==============================
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/item/{item_id}', [ProductController::class, 'show'])->name('products.show');