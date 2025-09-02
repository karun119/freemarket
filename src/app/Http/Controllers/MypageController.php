<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;



class MypageController extends Controller
{
    public function index(Request $request)
{
    $user = auth()->user();
    $profile = $user->profile;

    // クエリパラメータ ?page=sell or ?page=buy を判断
    $viewType = $request->query('page', 'sell');

    if ($viewType === 'buy') {
        $orders = $user->orders()->with('product')->get();
        return view('mypage', compact('user', 'profile', 'viewType', 'orders'));
    } else {
        $products = $user->products()->get();
        return view('mypage', compact('user', 'profile', 'viewType', 'products'));
    }
}

    public function edit()
    {
        $user = auth()->user();

        // 初回ログイン時、プロフィールがなければ作成
        if (!$user->profile) {
            $user->profile()->create([
                'postal_code' => '',
                'address' => '',
                'building' => '',
                'image_path' => null,
            ]);
        }
        $user->load('profile');

        return view('profile_edit', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        $profile = $user->profile;

        // 画像アップロード処理
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $path = $image->store('profile_images', 'public');

            // 古い画像があれば削除
            if ($profile->image_path && Storage::disk('public')->exists($profile->image_path)) {
                Storage::disk('public')->delete($profile->image_path);
            }

            $profile->image_path = $path;
        }

        // ユーザー名更新
        $user->name = $request->name;
        $user->save();

        // プロフィール更新
        $profile->postal_code = $request->postal_code;
        $profile->address = $request->address;
        $profile->building = $request->building;
        $profile->save();

        // 更新後の画面表示
        return redirect(('/?tab=mylist'))->with('success', 'プロフィールを更新しました。');
    
    }

}
