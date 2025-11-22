<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // 編集画面
    public function edit(Request $request)
    {
        $user = Auth::user();
        $from = $request->query('from', 'edit');
        $redirectKey = $from === 'register' ? 'top' : 'mypage';

        return view('profile.edit', [
            'user' => $user,
            'redirectKey' => $redirectKey,
        ]);
    }

    // 更新
    public function update(ProfileRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        // 画像が来ていれば保存（public ディスク）
        if ($request->hasFile('profile_image')) {
            $newPath = $request->file('profile_image')->store('profiles', 'public');

            // 旧画像を削除
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $newPath;
        }

        $user->update($validated);

        $redirectKey = $request->input('redirect_key', 'mypage');

        if ($redirectKey === 'top') {
            return redirect()->route('products.index', ['tab' => 'mylist'])
                ->with('success', 'プロフィールを更新しました');
        }

        return redirect()->route('mypage.index')
            ->with('success', 'プロフィールを更新しました');
    }
}
