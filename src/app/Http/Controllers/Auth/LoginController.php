<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
        public function loginView()
{
    return view('auth.login');
}


    public function store(LoginRequest $request)
{
    // Fortify が内部で実行する認証ロジック
    // $credentials = $request->only('email', 'password');

    // if (!Auth::attempt($credentials)) {
    //     return back();
    // }
    $request->authenticate();

    // ✅ メール認証チェック（ログアウトせず）
    if (! Auth::user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice')->withErrors([
            'email' => 'メールアドレスの確認が完了していません。メールをご確認ください。',
        ]);
    }

    $request->session()->regenerate();

    return redirect()->intended('/?tab=mylist');
}

}
