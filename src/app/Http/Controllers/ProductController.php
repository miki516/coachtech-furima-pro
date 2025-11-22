<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $search = $request->input('q');


        // 1) URLにtabがあればそれをそのまま使う
        if ($request->has('tab')) {
            $tab = $request->query('tab') === 'mylist' ? 'mylist' : 'recommend';
        } else {
            // 2) 未指定ならおすすめ
            $tab = 'recommend';
        }

        // 3) /?tab=recommend を / に正規化
        if ($request->query('tab') === 'recommend') {
            return redirect()->to('/' . ($search ? '?q='.$search : ''));
        }

        if ($tab === 'mylist') {
            if (auth()->check()) {
                $query = auth()->user()->favoriteProducts()->with('categories');

                if ($search) {
                    $query->where('name', 'like', "%{$search}%");
                }

                $products = $query->get();
            } else {
                $products = collect();
            }
        } else {
            $query = Product::with('categories')
                ->when(Auth::check(), fn($q) => $q->where('seller_id', '!=', Auth::id()));

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $products = $query->get();
            $tab = 'recommend';
        }

        return view('products.index', [
            'products' => $products,
            'defaultTab' => $tab,
            'query' => $search,
        ]);
    }

    public function show($item_id)
    {
        $product = Product::with(['categories', 'user', 'comments.user'])->findOrFail($item_id);

        $isFavorited = false;
        if (auth()->check()) {
        $isFavorited = $product->favorites()->where('user_id', auth()->id())->exists();
        }

        return view('products.show', compact('product', 'isFavorited'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('products.create', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $data = $request->validated();

        // 画像保存（storage/app/public/products 配下）
        $data['image_path'] = $request->file('image')->store('products', 'public');

        // 出品者
        $data['seller_id'] = Auth::id();

        $product = Product::create($data);

        // カテゴリ紐付け（チェックボックス配列を中間テーブルに保存）
        $product->categories()->sync($request->input('category_id', []));

        return redirect()->route('products.index')->with('success', '商品を出品しました');
    }

    public function toggleFavorite($productId)
    {
        $user = Auth::user();
        $user->favoriteProducts()->toggle($productId);

        return redirect()->route('products.show', $productId);
    }
}