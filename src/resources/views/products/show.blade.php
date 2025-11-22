@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/products/show.css') }}">
@endsection

@section('content')
    @php($isFavorited = $isFavorited ?? false)

    <div class="product-detail">
        {{-- 左カラム（商品画像） --}}
        <div class="product-detail-image">
            <img class="product-detail-img" src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}">
        </div>

        {{-- 右カラム（商品情報） --}}
        <div class="product-detail-info">
            <h1 class="product-detail-title">{{ $product->name }}</h1>
            <p class="product-brand">{{ $product->brand ?? 'ブランド不明' }}</p>
            <p class="product-price">
                <span class="product-price-yen">¥</span>
                {{ number_format($product->price) }}
                <span class="product-price-tax">(税込)</span>
            </p>

            <div class="product-actions">
                {{-- お気に入りボタン --}}
                <form action="{{ route('products.favorite', $product->id) }}" method="post">
                    @csrf
                    <button type="submit" class="icon-button">
                        <span class="icon-box">
                            <img src="{{ asset($isFavorited ? 'images/icons/star-active.svg' : 'images/icons/star-inactive.svg') }}"
                                alt="お気に入り">
                        </span>
                        <span class="icon-count">{{ $product->favorites()->count() }}</span>
                    </button>
                </form>

                {{-- コメントボタン --}}
                <a href="#comments" class="icon-button ">
                    <span class="icon-box">
                        <img src="{{ asset('images/icons/comment.png') }}" alt="コメント">
                    </span>
                    <span class="icon-count">{{ $product->comments()->count() }}</span>
                </a>
            </div>

            @if ($product->status === 'sold')
                <button class="btn btn-solid product-detail-purchase-btn" disabled>Sold</button>
            @else
                <a href="{{ route('purchase.create', ['product' => $product->id]) }}"
                    class="btn btn-solid product-detail-purchase-btn">購入手続きへ</a>
            @endif

            <h3 class="product-detail-subtitle">商品説明</h3>
            <p class="product-detail-description">{{ $product->description }}</p>

            <h3 class="product-detail-subtitle">商品の情報</h3>

            <dl class="pd-info">
                <div class="pd-info-row">
                    <dt class="pd-info-term">カテゴリー</dt>
                    <dd class="pd-info-desc">
                        @foreach ($product->categories as $category)
                            <span class="tag">{{ $category->name }}</span>
                        @endforeach
                    </dd>
                </div>

                <div class="pd-info-row">
                    <dt class="pd-info-term">商品の状態</dt>
                    <dd class="pd-info-desc">{{ $product->condition }}</dd>
                </div>
            </dl>

            <h3 class="product-detail-subtitle product-detail-subtitle-comment">コメント ({{ $product->comments->count() }})
            </h3>
            {{-- コメント一覧 --}}
            <div class="comments" id="comments">
                {{-- コメントがある場合は表示、ない場合はメッセージ表示 --}}
                @forelse ($product->comments as $comment)
                    <div class="comment-header">
                        {{-- ユーザーアイコン --}}
                        @if ($comment->user->profile_image)
                            <img src="{{ asset('storage/' . $comment->user->profile_image) }}"
                                alt="{{ $comment->user->name }}" class="avatar">
                        @else
                            <div class="avatar default-avatar"></div>
                        @endif
                        <div class="comment-username">
                            <strong>{{ $comment->user->name }}</strong>
                        </div>
                    </div>
                    {{-- コメント内容 --}}
                    <div class="comment-body">
                        <p class="comment-text">{{ $comment->content }}</p>
                    </div>
                @empty
                    <p>まだコメントはありません。</p>
                @endforelse
            </div>
            {{-- コメント投稿フォーム --}}
            <h4 class="product-detail-comment-title">商品へのコメント</h4>
            <form action="{{ route('comments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <textarea class="product-detail-comment-textarea" name="content"></textarea>
                @isset($errors)
                    @error('content')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                @endisset
                <button class="btn btn-solid product-detail-comment-btn" type="submit">コメントを送信する</button>
            </form>


        </div>
    </div>
@endsection
