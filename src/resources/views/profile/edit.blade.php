@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/form.css') }}">
@endsection

@section('content')
    <div class="profile-settings">
        <h1 class="page-title">プロフィール設定</h1>
        <div class="form-content">
            <div class="form-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <div class="profile-image-row">
                            @if ($user->profile_image)
                                <div id="avatarPreview" class="profile-avatar has-image"
                                    style="background-image:url('{{ asset('storage/' . $user->profile_image) }}')"></div>
                            @else
                                <div id="avatarPreview" class="profile-avatar"></div>
                            @endif
                            <div>
                                <label for="profile_image" class="btn btn-outline form-file-label">画像を選択する</label>
                                <input id="profile_image" type="file" name="profile_image" accept="image/jpeg,image/png"
                                    hidden>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-group-title">
                            <label class="form-label-item" for="name">ユーザー名</label>
                        </div>
                        <input class="field-control" id="name" type="text" name="name"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-group-title"><label class="form-label-item" for="postal_code">郵便番号</label>
                        </div>
                        <input class="field-control" id="postal_code" type="text" name="postal_code" inputmode="numeric"
                            autocomplete="postal-code" value="{{ old('postal_code', $user->postal_code) }}">
                        @error('postal_code')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-group-title">
                            <label class="form-label-item" for="address">住所</label>
                        </div>
                        <input class="field-control" id="address" type="text" name="address"
                            value="{{ old('address', $user->address) }}">
                        @error('address')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-group-title">
                            <label class="form-label-item" for="building">建物名</label>
                        </div>
                        <input class="field-control" id="building" type="text" name="building"
                            value="{{ old('building', $user->building) }}">
                    </div>
            </div>
            <div class="form-actions">
                {{-- 更新後の戻り先キー（top / mypage） --}}
                <input type="hidden" name="redirect_key" value="{{ $redirectKey }}">

                <button class="btn btn-solid auth-form-submit" type="submit">更新する</button>
            </div>
            </form>
        </div>
    </div>
    {{-- 画像選択の即時プレビュー（ビルド不要の素JS） --}}
    <script>
        (function() {
            const input = document.getElementById('profile_image');
            const preview = document.getElementById('avatarPreview');
            if (!input || !preview) return;

            input.addEventListener('change', (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                const url = URL.createObjectURL(file);
                // 背景画像として表示
                preview.style.backgroundImage = `url('${url}')`;
                preview.classList.add('has-image');

                // メモリ解放（画像の読み込み完了後）
                const img = new Image();
                img.onload = () => URL.revokeObjectURL(url);
                img.src = url;
            });
        })();
    </script>
@endsection
