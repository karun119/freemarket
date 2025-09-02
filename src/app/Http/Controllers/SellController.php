<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ExhibitionRequest;

class SellController extends Controller
{
    public function create()
    {
                // N+1対策：カテゴリと状態を一括取得
        $categories = Category::all();
        $conditions = Condition::all();

        return view('exhibition', compact('categories', 'conditions'));

    }

    public function store(ExhibitionRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = auth()->user();

             // 画像アップロード
            $imagePath = null;
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('product_images', 'public');
            }

             // Product作成
            $product = Product::create([
                'user_id' => $user->id,
                'condition_id' => $request->condition_id,
                'item_name' => $request->item_name,
                'brand' => $request->brand,
                'description' => $request->description,
                'price' => $request->price,
                'image_path' => $imagePath,
            ]);

             // カテゴリ紐付け（中間テーブル）
            if ($request->categories) {
                $product->categories()->attach($request->categories);
            }
        });

        return redirect()->route('mypage.index')->with('success', '商品を出品しました');
    }
}
