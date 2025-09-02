<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    /**
     * 商品購入画面表示
     */
    public function show($item_id)
{
    $product = Product::findOrFail($item_id);
    $profile = Auth::user()->profile;
    // セッションから支払い方法取得（なければ空）
    $paymentMethod = session('payment_method.' . $item_id, '');

    return view('purchase', compact('product', 'profile', 'paymentMethod'));
}

    public function store(PurchaseRequest $request, $item_id)
{
    $validated = $request->validated();
    session(['payment_method.' . $item_id => $request->payment_method]);

    $product = Product::findOrFail($item_id);

    // すでに購入済みチェック
    if (Order::where('product_id', $product->id)->exists()) {
        return redirect()->route('items.index')->with('error', 'この商品はすでに購入済みです。');;
    }

    // 注文を保存
    $order = Order::create(array_merge($validated, [
        'user_id'    => Auth::id(),
        'product_id' => $product->id,
    ]));

    // Stripe API キー設定
    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    // 支払い方法ごとにセッション作成
    if ($request->payment_method === 'card') {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->item_name,
                    ],
                    'unit_amount' => $product->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('items.index') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('items.index'),
        ]);
    } elseif ($request->payment_method === 'convenience') {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['konbini'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->item_name,
                    ],
                    'unit_amount' => $product->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('items.index') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('items.index'),
        ]);
    } else {
        return redirect()->route('items.index')->with('error', '支払い方法が不正です');
    }

    // Stripe決済画面へリダイレクト
    return redirect($session->url);
}


    

     public function editAddress(Product $item, Request $request)
    {
    // URLで payment_method が渡されていればセッションに保存
    if ($request->filled('payment_method')) {
        session(['payment_method.' . $item->id => $request->payment_method]);
    }

    
    return view('address_edit', compact('item'));
    }

    public function updateAddress(AddressRequest $request, Product $item)
{
    

    // 住所だけセッションに保存、支払い方法は既存値をそのまま保持
    session([
        'sending_postcode' => $request->sending_postcode,
        'sending_address'  => $request->sending_address,
        'sending_building' => $request->sending_building,
    ]);

    return redirect()->route('purchase.show', $item->id);
}





}

   


