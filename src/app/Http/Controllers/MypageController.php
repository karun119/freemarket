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
        $viewType = $request->query('page', 'sell');

        if ($viewType === 'buy') {
            $data = ['orders' => $user->orders()->with('product')->get()];
        } else {
            $data = ['products' => $user->products()->get()];
        }

        return view('mypage', array_merge(['user' => $user, 'profile' => $profile, 'viewType' => $viewType], $data));
    }


    public function edit()
    {
        $user = auth()->user();

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

        if ($request->hasFile('profile_image')) {
            if ($profile->image_path && Storage::disk('public')->exists($profile->image_path)) {
                Storage::disk('public')->delete($profile->image_path);
            }

            $profile->image_path = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user->update(['name' => $request->name]);
        $profile->update([
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
        ]);

        return redirect('/?tab=mylist')->with('success', 'プロフィールを更新しました。');
    }
}
