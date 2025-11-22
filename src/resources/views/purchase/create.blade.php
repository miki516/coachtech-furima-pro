@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase/create.css') }}">
@endsection

@section('content')
    <form action="{{ route('purchase.store', $product->id) }}" method="POST">
        @csrf

        <div class="purchase-container">

            {{-- 左カラム --}}
            <div class="purchase-left">
                {{-- 商品情報 --}}
                <div class="product-summary">
                    <img class="product-summary-img" src="{{ asset('storage/' . $product->image_path) }}"
                        alt="{{ $product->name }}">
                    <div>
                        <h2 class="product-summary-title">{{ $product->name }}</h2>
                        <p class="product-summary-price"><span
                                class="product-summary-price-label">¥</span>{{ number_format($product->price) }}</p>
                    </div>
                </div>

                <hr class="divider">
                {{-- 支払い方法 --}}
                <div class="payment-method">
                    <h3 class="payment-method-title">支払い方法</h3>
                    <div class="payment-method-input-area">
                        {{-- 支払い方法セレクト --}}
                        <select class="payment-method-select" name="payment_method_id" id="payment_method">
                            <option value="" selected disabled hidden>選択してください</option>
                            <option value="1">コンビニ払い</option>
                            <option value="2">カード支払い</option>
                        </select>
                        @error('payment_method_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="divider">

                {{-- 配送先 --}}
                <div class="shipping-address">
                    <div class="shipping-address-header">
                        <h3 class="shipping-address-title">配送先</h3>
                        <a class="shipping-address-edit" href="{{ route('purchase.address.edit', $product->id) }}">変更する</a>
                    </div>
                    <address class="shipping-address-info">{{ $shipping['postal_code'] }}<br>
                        {{ $shipping['address'] }}<br>
                        {{ $shipping['building'] }}
                    </address>
                </div>

                <hr class="divider">

            </div>

            {{-- 右カラム --}}
            <div class="purchase-right">
                <div class="purchase-summary" role="group" aria-label="購入サマリー">
                    <dl class="summary-list">
                        <div class="summary-row">
                            <dt class="summary-row-title">商品代金</dt>
                            <dd class="summary-row-value"><span>¥</span>{{ number_format($product->price) }}</dd>
                        </div>
                        <div class="summary-row">
                            <dt class="summary-row-title">支払い方法</dt>
                            <dd id="selected-method" class="summary-row-value">選択してください</dd>
                        </div>
                    </dl>
                </div>

                <button class="btn btn-solid btn-purchase">購入する</button>
            </div>
    </form>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('payment_method');
            const display = document.getElementById('selected-method');

            const updateDisplay = () => {
                if (select.value === "1") {
                    display.textContent = "コンビニ払い";
                    display.style.display = "block"; // 表示
                } else if (select.value === "2") {
                    display.textContent = "カード支払い";
                    display.style.display = "block"; // 表示
                } else {
                    display.textContent = "";
                    display.style.display = "none"; // 非表示
                }
            };

            // 初期表示も反映
            updateDisplay();

            // セレクト変更時に反映
            select.addEventListener('change', updateDisplay);
        });
    </script>
@endsection
