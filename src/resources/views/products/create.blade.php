@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/products/create.css') }}">
@endsection

@section('content')
    <div class="sell-container">
        <h1 class="page-title">商品の出品</h1>

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="sell-form" novalidate>
            @csrf

            {{-- 画像エリア --}}
            <section class="block">
                <label for="image" class="form-label-item form-label-image">商品画像</label>

                <div class="drop-wrap" id="dropWrap">
                    <div class="dropzone" id="dropzone">
                        <img id="productPreview" alt="商品画像プレビュー">
                        <div class="placeholder"></div>
                    </div>

                    {{-- 初期：中央に重ねる／選択後：枠の下に移動 --}}
                    <button type="button" class="btn btn-outline pick-btn" id="pickImageBtn">画像を選択する</button>
                    <input id="image" type="file" name="image" accept="image/*" hidden>
                </div>

                @error('image')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </section>

            {{-- 商品の詳細 --}}
            <section class="block">
                <h2 class="block-title">商品の詳細</h2>

                <div class="row">
                    <label class="form-label-item">カテゴリー</label>
                    <div class="tag-list">
                        @foreach ($categories as $category)
                            <label class="tag">
                                <input type="checkbox" name="category_id[]" value="{{ $category->id }}"
                                    {{ in_array($category->id, old('category_id', [])) ? 'checked' : '' }}>
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('category_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row">
                    <label for="condition" class="form-label-item">商品の状態</label>
                    @php($cur = old('condition', ''))
                    <select name="condition" id="condition" class="select">
                        <option value="" selected disabled hidden>選択してください</option>
                        <option value="良好" @selected(old('condition') === '良好')>良好</option>
                        <option value="目立った傷や汚れなし" @selected(old('condition') === '目立った傷や汚れなし')>目立った傷や汚れなし</option>
                        <option value="やや傷や汚れあり" @selected(old('condition') === 'やや傷や汚れあり')>やや傷や汚れあり</option>
                        <option value="状態が悪い" @selected(old('condition') === '状態が悪い')>状態が悪い</option>
                    </select>
                    @error('condition')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- 商品名と説明 --}}
            <section class="block">
                <h2 class="block-title">商品名と説明</h2>

                <div class="row">
                    <label for="name" class="form-label-item">商品名</label>
                    <input id="name" type="text" name="name" class="input" value="{{ old('name') }}">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row">
                    <label for="brand" class="form-label-item">ブランド名</label>
                    <input id="brand" type="text" name="brand" class="input" value="{{ old('brand') }}">
                    @error('brand')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row">
                    <label for="description" class="form-label-item">商品の説明</label>
                    <textarea id="description" name="description" class="textarea" rows="5">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row">
                    <label for="price" class="form-label-item">販売価格</label>
                    <div class="price-wrap">
                        <span class="yen">¥</span>
                        <input id="price" type="number" name="price" class="input price" value="{{ old('price') }}"
                            min="0" step="1">
                    </div>
                    @error('price')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <div class="actions">
                <button type="submit" class="btn btn-solid">出品する</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrap = document.getElementById('dropWrap');
            const input = document.getElementById('image');
            const btn = document.getElementById('pickImageBtn');
            const preview = document.getElementById('productPreview');

            btn.addEventListener('click', () => input.click());

            input.addEventListener('change', (e) => {
                const file = e.target.files?.[0];
                if (!file) {
                    preview.removeAttribute('src');
                    wrap.classList.remove('has-image');
                    btn.textContent = '画像を選択する';
                    return;
                }
                const url = URL.createObjectURL(file);
                preview.src = url;
                wrap.classList.add('has-image');
                btn.textContent = '画像を変更する';
                preview.onload = () => URL.revokeObjectURL(url);
            });
        });
    </script>
@endsection
