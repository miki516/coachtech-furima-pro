@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase/create.css') }}">
@endsection

@section('content')
    <div class="address-edit-page">
        <h1 class="page-title">住所の変更</h1>
        <div class="form-content">
            <div class="form-body">
                <form class="form" action="{{ route('purchase.address.update', $product->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <div class="form-group-title">
                            <label class="form-label-item">郵便番号</label>
                        </div>
                        <input class="field-control" type="text" name="postal_code"
                            value="{{ session('shipping_postal_code', old('postal_code', $user->postal_code)) }}">
                        @error('postal_code')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-group-title">
                            <label class="form-label-item">住所</label>
                        </div>
                        <input class="field-control" type="text" name="address"
                            value="{{ session('shipping_address', old('address', $user->address)) }}">
                        @error('address')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-group-title">
                            <label class="form-label-item">建物名</label>
                        </div>
                        <input class="field-control" type="text" name="building"
                            value="{{ session('shipping_building', old('building', $user->building)) }}">
                    </div>
            </div>
            <button class="btn btn-solid address-update" type="submit">更新する</button>
            </form>
        </div>
    </div>
@endsection
