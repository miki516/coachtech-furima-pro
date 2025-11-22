<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 必須項目のいずれかが空ならプロフィール編集へリダイレクト
        if (
            empty($user->name) ||
            empty($user->postal_code) ||
            empty($user->address)
        ) {
            return redirect()->route('profile.edit')
                ->with('message', 'プロフィールを入力してください');
        }

        return $next($request);
    }
}