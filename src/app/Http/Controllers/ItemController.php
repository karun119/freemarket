<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;


class ItemController extends Controller
{
  public function index(Request $request)
    {
    $tab = $request->query('tab', 'recommend');
    $keyword = $request->query('keyword');

    if ($tab === 'mylist') {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $query = $user->favoriteProducts()
                ->where('products.user_id', '!=', $user->id)
                ->with('order')
                ->searchByName($keyword);

        $products = $query->latest()
                ->get();

    } else {
        $query = Product::query();
        if (Auth::check()) {
            $query->where('user_id', '!=', Auth::id());
        }
         $products = $query->searchByName($keyword)
                ->latest()
                ->get();
    }
    // 送付先住所のリセット
        session()->forget(['sending_postcode', 'sending_address', 'sending_building']);

        // 商品ごとの支払い方法をリセット
        foreach ($products as $product) {
            session()->forget('payment_method.' . $product->id);
        }
        
    return view('index', compact('products', 'tab'));
    }
    // GET
public function show(Product $item)
{
    // 関連データをまとめてロード
    $item->load([
        'condition',
        'categories',
        'comments' => function($query) {
        $query->with('user.profile');
        },
        'favoritedByUsers',
    ]);

    return view('product', ['product' => $item]);
}

// POST
public function store(Request $request, Product $item)
{
    $user = $request->user();

    $action = $request->input('action');

    if ($action === 'toggle_favorite') {
        // いいねトグル処理
        if ($item->favoritedByUsers()->where('user_id', $user->id)->exists()) {
            $item->favoritedByUsers()->detach($user->id);
        } else {
            $item->favoritedByUsers()->attach($user->id);
        }

    } elseif ($action === 'comment') {
        $validated = app(CommentRequest::class)->validateResolved();
        // コメント作成処理
        $item->comments()->create([
            'user_id' => $user->id,
            'comment' => $request->input('comment'),
        ]);
    }

    // PRGパターンでリダイレクト
    return redirect()->route('item.show', $item->id);
}


}


