@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/trade/chat.css') }}">
@endsection

@section('content')
    <div class="trade-layout">

        @php
            // 自分が買い手か売り手か
            $isBuyer = $order->buyer_id === $user->id;

            // 売り手側の「自動モーダル表示」条件
            $autoOpenRatingModal = !$isBuyer && $order->buyer_reviewed && !$order->seller_reviewed;
        @endphp

        {{-- サイドメニュー（共通） --}}
        <aside class="trade-sidebar">
            <div class="sidebar-title">その他の取引</div>

            @foreach ($sideTrades as $t)
                <a href="{{ route('trade.chat', ['order' => $t->id]) }}"
                    class="sidebar-item {{ $t->id === $order->id ? 'active' : '' }}">
                    {{ $t->product->name }}
                </a>
            @endforeach
        </aside>

        {{-- メインチャットエリア --}}
        <main class="trade-main">

            {{-- 上部ヘッダー --}}
            <div class="trade-header">
                <div class="trade-header-left">
                    @php
                        // 取引相手ユーザー
                        $partner = $order->product->seller_id === $user->id ? $order->buyer : $order->product->seller;

                        // アイコンパス（なければデフォルト）
                        $iconPath = $partner->profile_image ?? 'profiles/default.png';
                    @endphp

                    <div class="user-icon">
                        <img src="{{ asset('storage/' . $iconPath) }}" alt="{{ $partner->name }} のアイコン">
                    </div>

                    <span class="trade-header-title">
                        「{{ $partner->name }}」さんとの取引画面
                    </span>
                </div>


                {{-- 購入者だけ「取引を完了する」ボタン --}}
                @if ($isBuyer && !$order->buyer_reviewed)
                    <button class="btn-complete" id="openRatingModal">取引を完了する</button>
                @endif
            </div>

            {{-- 商品情報 --}}
            <div class="trade-product-box">
                <img src="{{ asset('storage/' . $order->product->image_path) }}" class="product-image">
                <div class="product-info">
                    <h2 class="product-name">{{ $order->product->name }}</h2>
                    <p class="product-price">{{ number_format($order->product->price) }}円</p>
                </div>
            </div>

            {{-- メッセージ一覧 --}}
            <div class="trade-messages">
                @foreach ($messages as $msg)
                    <div id="message-{{ $msg->id }}"
                        class="message-row {{ $msg->sender_id === $user->id ? 'me' : 'other' }}">

                        {{-- ヘッダー：アイコン + ユーザー名 --}}
                        <div class="message-header">
                            <div class="user-icon-sm">
                                @php
                                    // アイコンパス（なければデフォルト画像）
                                    $iconPath = $msg->sender->profile_image ?? 'profiles/default.png';
                                @endphp

                                <img src="{{ asset('storage/' . $iconPath) }}" alt="{{ $msg->sender->name }} のアイコン">
                            </div>
                            <div class="message-user">{{ $msg->sender->name }}</div>
                        </div>

                        {{-- 吹き出し（本文＋画像） --}}
                        <div class="message-bubble" id="message-{{ $msg->id }}"data-message-id="{{ $msg->id }}">
                            {{-- 通常表示部分 --}}
                            <div class="message-text" data-message-text>{{ $msg->message }}</div>

                            {{-- 画像があれば表示 --}}
                            @if ($msg->image_path)
                                <div class="message-image">
                                    <img src="{{ asset('storage/' . $msg->image_path) }}" alt="取引メッセージ画像">
                                </div>
                            @endif
                        </div>

                        {{-- 自分のメッセージだけ編集/削除 --}}
                        @if ($msg->sender_id === $user->id)
                            {{-- 編集フォーム（最初は非表示） --}}
                            <form action="{{ route('trade.message.update', ['message' => $msg->id]) }}" method="POST"
                                class="message-edit-form">
                                @csrf
                                @method('PATCH')

                                <textarea name="message" class="message-edit-textarea" maxlength="400">{{ $msg->message }}</textarea>

                                @if ($msg->image_path)
                                    <div class="message-edit-image-wrapper">
                                        <div class="message-edit-image">
                                            <img src="{{ asset('storage/' . $msg->image_path) }}" alt="取引メッセージ画像">
                                            <button type="button" class="message-edit-image-remove"
                                                aria-label="画像を削除">×</button>
                                        </div>
                                        {{-- 画像削除フラグ --}}
                                        <input type="hidden" name="delete_image" value="0" class="delete-image-input">
                                    </div>
                                @endif

                                <div class="message-edit-actions">
                                    <button type="button" class="message-edit-cancel">キャンセル</button>
                                    <button type="submit" class="message-edit-save">保存</button>
                                </div>
                            </form>

                            {{-- 編集 / 削除ボタン --}}
                            <div class="message-actions">
                                <button type="button" class="edit-btn">編集</button>

                                <form action="{{ route('trade.message.destroy', ['message' => $msg->id]) }}" method="POST"
                                    class="message-delete-form" onsubmit="return confirm('このメッセージを削除しますか？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn">削除</button>
                                </form>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>


            {{-- 入力フォーム --}}
            <form action="{{ route('trade.message.store', ['order' => $order->id]) }}" method="POST" class="trade-form"
                enctype="multipart/form-data" id="trade-form">
                @csrf
                <div class="form-errors">
                    @if ($errors->has('message') || $errors->has('image'))
                        @foreach ($errors->get('message') as $error)
                            <p class="form-error">{{ $error }}</p>
                        @endforeach
                        @foreach ($errors->get('image') as $error)
                            <p class="form-error">{{ $error }}</p>
                        @endforeach
                    @endif
                </div>
                <div class="trade-form-controls">
                    <textarea name="message" id="tradeMessageInput" placeholder="取引メッセージを記入してください">{{ old('message') }}</textarea>

                    {{-- プレビュー枠 --}}
                    <div class="image-preview" id="imagePreview" aria-hidden="true">
                        <img src="" alt="選択された画像のプレビュー" id="imagePreviewImg">
                        <button type="button" class="image-preview-remove" id="imagePreviewRemove">×</button>
                    </div>

                    <label class="image-add-btn">
                        画像を追加
                        <input type="file" name="image" id="imageInput" accept="image/*" hidden>
                    </label>

                    <button type="submit" class="send-btn">
                        <img src="/images/icons/send.png" alt="send" class="send-icon">
                    </button>
                </div>
            </form>
        </main>
    </div>

    {{-- 評価モーダル --}}
    <div class="rating-modal" id="ratingModal" aria-hidden="true"
        data-auto-open="{{ $autoOpenRatingModal ? '1' : '0' }}">
        <div class="rating-modal-backdrop"></div>

        <div class="rating-modal-body" role="dialog" aria-modal="true">
            {{-- タイトル＋上のボーダー --}}
            <div class="rating-modal-header">
                <h2 class="rating-modal-title">取引が完了しました。</h2>
            </div>

            {{-- 質問テキスト＋星エリア（下にボーダー） --}}
            <div class="rating-modal-main">
                <p class="rating-modal-text">今回の取引相手はどうでしたか？</p>

                <div class="rating-stars" id="ratingStars">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" class="rating-star" data-value="{{ $i }}">★</button>
                    @endfor
                </div>
            </div>

            {{-- エラー & ボタンエリア --}}
            <form action="{{ route('trade.review.store', ['order' => $order->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="rating" id="ratingValue">

                @error('rating')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <div class="rating-modal-actions">
                    <button type="submit" class="rating-modal-submit-btn" id="submitRatingButton" disabled>
                        送信する
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        (function() {
            const modal = document.getElementById('ratingModal');
            if (!modal) return;

            const openButton = document.getElementById('openRatingModal'); // 購入者用
            const backdrop = modal.querySelector('.rating-modal-backdrop');
            const stars = modal.querySelectorAll('.rating-star');
            const ratingInput = document.getElementById('ratingValue');
            const submitButton = document.getElementById('submitRatingButton');
            const shouldAutoOpen = modal.dataset.autoOpen === '1'; // 売り手用

            function openModal() {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            // 「取引を完了する」ボタン（購入者）
            if (openButton) {
                openButton.addEventListener('click', openModal);
            }

            // 売り手側：画面を開いたら自動でモーダル表示
            if (shouldAutoOpen) {
                openModal();
            }

            // 背景クリックでモーダル閉じる
            if (backdrop) {
                backdrop.addEventListener('click', function(e) {
                    if (e.target === backdrop) {
                        closeModal();
                    }
                });
            }

            // 星クリックで選択＆ハイライト
            stars.forEach(function(star) {
                star.addEventListener('click', function() {
                    const value = Number(this.dataset.value);
                    ratingInput.value = value;
                    submitButton.disabled = false;

                    stars.forEach(function(s) {
                        const v = Number(s.dataset.value);
                        s.classList.toggle('is-active', v <= value);
                    });
                });
            });

            // ============================
            // メッセージ編集用の JS
            // ============================
            const editButtons = document.querySelectorAll('.edit-btn');
            const cancelButtons = document.querySelectorAll('.message-edit-cancel');

            // 「編集」ボタン
            editButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const row = this.closest('.message-row');
                    if (!row) return;

                    const bubble = row.querySelector('.message-bubble');
                    const form = row.querySelector('.message-edit-form');
                    if (!bubble || !form) return;

                    const textEl = bubble.querySelector('[data-message-text]');
                    if (!textEl) return;

                    // ★ 吹き出しごと非表示にする
                    bubble.style.display = 'none';

                    // 編集フォームを表示
                    form.style.display = 'block';

                    const textarea = form.querySelector('.message-edit-textarea');
                    if (textarea) {
                        textarea.focus();
                        const len = textarea.value.length;
                        textarea.setSelectionRange(len, len);
                    }
                });
            });


            // 「キャンセル」ボタン
            cancelButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const row = this.closest('.message-row');
                    if (!row) return;

                    const bubble = row.querySelector('.message-bubble');
                    const form = row.querySelector('.message-edit-form');
                    if (!bubble || !form) return;

                    const textEl = bubble.querySelector('[data-message-text]');

                    // ★ 編集フォームを閉じて吹き出しを戻す
                    form.style.display = 'none';
                    bubble.style.display = 'block';

                    if (textEl) {
                        textEl.style.display = '';
                    }
                });
            });

            // ============================
            // メッセージ画像の削除（編集時）
            // ============================
            document.querySelectorAll('.message-edit-image-remove').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const wrapper = this.closest('.message-edit-image-wrapper');
                    if (!wrapper) return;

                    const hidden = wrapper.querySelector('.delete-image-input');
                    if (hidden) {
                        hidden.value = '1'; // 削除フラグ ON
                    }

                    // 画面上からも非表示にしておく
                    wrapper.style.display = 'none';
                });
            });

            // ============================
            // 画像プレビュー表示
            // ============================
            const imageInput = document.getElementById('imageInput');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('imagePreviewImg');
            const previewRemove = document.getElementById('imagePreviewRemove');

            if (imageInput && imagePreview && previewImg) {
                imageInput.addEventListener('change', function() {
                    const file = this.files && this.files[0];

                    if (!file) {
                        // ファイル未選択 or キャンセル
                        imagePreview.style.display = 'none';
                        previewImg.src = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                        imagePreview.setAttribute('aria-hidden', 'false');
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (previewRemove && imageInput && imagePreview && previewImg) {
                previewRemove.addEventListener('click', function() {
                    // 選択した画像をクリア
                    imageInput.value = '';
                    previewImg.src = '';
                    imagePreview.style.display = 'none';
                    imagePreview.setAttribute('aria-hidden', 'true');
                });
            }

            // ============================
            // 入力情報保持（本文のみ）
            // ============================
            const messageInput = document.getElementById('tradeMessageInput');

            if (messageInput) {
                // orderごとに別のキーにしておく
                const storageKey = 'trade_message_draft_order_{{ $order->id }}';

                // 初期表示時
                if (messageInput.value.trim() === '') {
                    const saved = localStorage.getItem(storageKey);
                    if (saved !== null) {
                        messageInput.value = saved;
                    }
                }

                // 入力のたびに localStorage に保存
                messageInput.addEventListener('input', function() {
                    localStorage.setItem(storageKey, this.value);
                });

                // フォーム送信時は「送信完了扱い」としてドラフトを削除
                const form = messageInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        localStorage.removeItem(storageKey);
                    });
                }
            }
        })();
    </script>
@endsection
