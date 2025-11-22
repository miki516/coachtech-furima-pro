@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mypage/index.css') }}">
@endsection

@section('content')
    <h1 class="sr-only">mypage</h1>

    <div class="profile-header">
        <div class="profile-header-left">
            <div class="profile-header-avatar">
                @if ($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}のアイコン">
                @else
                    <div class="profile-header-avatar-placeholder" aria-hidden="true"></div>
                @endif
            </div>

            <div class="profile-header-text">
                <div class="profile-header-name">{{ $user->name }}</div>

                {{-- ★ 平均評価（評価が1件以上あるときだけ表示） --}}
                @if (($ratingCount ?? 0) > 0 && !is_null($averageRating ?? null))
                    <div class="profile-header-rating" aria-label="評価 {{ number_format($averageRating, 1) }} / 5">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="profile-header-rating-star {{ $i <= round($averageRating) ? 'is-active' : '' }}">
                                ★
                            </span>
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        <div class="profile-header-right">
            <div class="profile-header-actions">
                <a href="{{ route('profile.edit', ['from' => 'mypage']) }}" class="btn btn-outline profile-header-button">
                    プロフィールを編集
                </a>
            </div>
        </div>
    </div>


    {{-- タブ表示 --}}
    <div class="tabs-container">
        <ul class="tabs">
            <li class="tab {{ $activePage === 'sell' ? 'active' : '' }}">
                <a class="tab-link" href="{{ route('mypage.index', ['page' => 'sell']) }}">
                    出品した商品
                </a>
            </li>

            <li class="tab {{ $activePage === 'buy' ? 'active' : '' }}">
                <a class="tab-link" href="{{ route('mypage.index', ['page' => 'buy']) }}">
                    購入した商品
                </a>
            </li>
            {{-- 取引中タブ＋未読バッジ --}}
            <li class="tab {{ $activePage === 'trade' ? 'active' : '' }}">
                <a class="tab-link" href="{{ route('mypage.index', ['page' => 'trade']) }}">
                    取引中の商品
                    @if (($tradeUnreadCount ?? 0) > 0)
                        <span class="tab-badge">{{ $tradeUnreadCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    {{-- 商品一覧 --}}
    <div class="products-grid">
        @forelse ($items as $item)
            <div class="product-card">

                {{-- 未読バッジ（取引中タブのみ） --}}
                @if ($activePage === 'trade' && ($item->unreadMessagesCount ?? 0) > 0)
                    <div class="badge-unread">{{ $item->unreadMessagesCount }}</div>
                @endif

                {{-- タブごとに遷移先を変更 --}}
                @if ($activePage === 'trade')
                    <a href="{{ route('trade.chat', ['order' => $item->orderIdForTrade]) }}">
                    @else
                        <a href="{{ route('products.show', ['item_id' => $item->id]) }}">
                @endif
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                </a>

                <h3 class="product-card-name">{{ $item->name }}</h3>

                @if ($item->status === 'sold' && $activePage !== 'trade')
                    <span class="sold-label">Sold</span>
                @endif
            </div>
        @empty
            <p>表示する商品がありません</p>
        @endforelse
    </div>


    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection

@section('scripts')
    <script>
        // 戻る / 進む で表示されたときに発火
        window.addEventListener('pageshow', function(event) {
            // event.persisted: bfcache（戻るキャッシュ）から復元されたかどうか
            const navEntries = performance.getEntriesByType('navigation');
            const navType = navEntries[0] ? navEntries[0].type : null;

            if (event.persisted || navType === 'back_forward') {
                window.location.reload();
            }
        });
    </script>
@endsection
