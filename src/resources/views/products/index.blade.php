@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/products/index.css') }}">
@endsection

@section('content')
    <h1 class="sr-only">商品一覧</h1>

    <div class="tabs-container">
        <ul class="tabs">
            <li class="tab {{ $defaultTab === 'recommend' ? 'active' : '' }}">
                <a class="tab-link" href="{{ url('/' . (request('q') ? '?q=' . request('q') : '')) }}">おすすめ</a>
            </li>
            <li class="tab {{ $defaultTab === 'mylist' ? 'active' : '' }}">
                <a class="tab-link" href="{{ url('/?tab=mylist' . (request('q') ? '&q=' . request('q') : '')) }}">マイリスト</a>
            </li>
        </ul>
    </div>

    <div class="products-grid">
        @forelse ($products as $product)
            <div class="product-card">
                <a href="{{ route('products.show', ['item_id' => $product->id]) }}">
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}">
                </a>
                <h3 class="product-card-name">{{ $product->name }}</h3>
                @if ($product->status === 'sold')
                    <span class="sold-label">Sold</span>
                @endif
            </div>
        @empty
            <p>表示する商品がありません</p>
        @endforelse
    </div>
@endsection
