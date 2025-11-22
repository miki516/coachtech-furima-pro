<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>COACHTECH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    @yield('css')
</head>

<body class="site">
    @php
        if (!isset($errors)) {
            $errors = new \Illuminate\Support\ViewErrorBag();
        }
    @endphp
    <header class="site-header">
        <div class="site-header-logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </a>
        </div>

        {{-- 会員登録・ログイン・メール認証誘導では非表示 --}}
        @unless (Route::is('register') || Route::is('login') || Route::is('verification.notice'))
            <form action="{{ route('products.index') }}" method="GET" class="site-header-search">
                <input type="text" class="site-header-search-input" name="q" value="{{ request('q') }}"
                    placeholder="なにをお探しですか？">
            </form>

            <nav class="site-header-nav">
                {{-- ログイン or ログアウト --}}
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="site-header-logout-form">
                        @csrf
                        <button type="submit" class="site-header-logout-button">ログアウト</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="site-header-link">ログイン</a>
                @endauth

                {{-- マイページ --}}
                <a href="{{ route('mypage.index') }}" class="site-header-link">マイページ</a>

                {{-- 出品 --}}
                <a href="{{ route('products.create') }}" class="site-header-link site-header-link-sell">出品</a>
            </nav>
        @endunless
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    @yield('scripts')
</body>

</html>
