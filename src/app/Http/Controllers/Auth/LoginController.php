<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $request->authenticate();

        if (! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->withErrors([
                'email' => 'メールアドレスの確認が完了していません。メールをご確認ください。',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
