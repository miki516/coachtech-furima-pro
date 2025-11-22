<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(CommentRequest $request)
    {
        Comment::create([
            'content'    => $request->input('content'),
            'user_id'    => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        return back()->with('success', 'コメントが投稿されました。');
    }
}
