<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest as AppLoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 登録直後：メール認証誘導ページへ
        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse {
                public function toResponse($request)
                {
                    // メール認証の案内ページ（verification.notice）へ
                    return redirect()->route('verification.notice');
                }
            };
        });

        // ログイン直後：未認証なら誘導ページ、認証済みなら商品一覧へ
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $user = $request->user();
                    if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                        return redirect()->route('verification.notice');
                    }
                    return redirect()->intended(
                        route('products.index', ['tab' => 'mylist'])
                    );
                }
            };
        });

        // ログアウト直後はログイン画面へ
        $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    return redirect('/login');
                }
            };
        });
    }

    public function boot(): void
    {
        // ユーザー作成アクション（ここで登録バリデーション＆ユーザー作成）
        Fortify::createUsersUsing(CreateNewUser::class);

        // ビュー
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::loginView(fn () => view('auth.login'));

        // ログインの試行回数制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // 自作LoginRequestのルール＆メッセージでvalidateしてから認証
        Fortify::authenticateUsing(function (Request $request) {
            // 自作 LoginRequest をコンテナから解決し、rules/messages/attributes を使い回す
            $form = app(AppLoginRequest::class);

            $request->validate(
                $form->rules(),
                method_exists($form, 'messages') ? $form->messages() : [],
                method_exists($form, 'attributes') ? $form->attributes() : []
            );

            // 認証を試行（rememberにも対応）
            if (Auth::attempt($request->only('email', 'password'), (bool) $request->remember)) {
                return Auth::user(); // ← 成功時はユーザーを返す（未認証なら LoginResponse 側で誘導へ）
            }

            // 入力はあるが不一致（ID/パスワード違い）
            throw ValidationException::withMessages([
                'email' => ['ログイン情報が登録されていません'],
            ]);
        });
    }
}
