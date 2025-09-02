<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use App\Models\User;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 一般公開（認証不要）
Route::get('/', [ItemController::class, 'index'])->name('items.index');

Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// ログインページ表示（GET）
Route::get('/login', [LoginController::class, 'loginView'])->name('login');

// ログイン処理（POST）
Route::post('/login', [LoginController::class, 'store']);

// 商品詳細
Route::get('/item/{item}', [ItemController::class, 'show'])->name('item.show');


// 🔒ログイン不要なメール認証リンク処理（上書き！）
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    // 🔓一時的にログインさせて
    Auth::login($user);

    // 📬認証がまだならマークする
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect('/mypage/profile');
})->middleware(['signed'])->name('verification.verify');

// 👇これはログイン後用の通知画面（そのままでOK）
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '確認メールを再送しました。');
    })->name('verification.send');
});
// 認証必須
Route::middleware(['auth','verified'])->group(function () {
     // 認証必須のPOST
    Route::post('/item/{item}', [ItemController::class, 'store'])->name('item.store');
    // 商品購入
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])->name('purchase.store');

    // 送付先住所変更

    Route::get('/purchase/address/{item}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
    Route::post('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');


    // 商品出品
    Route::get('/sell', [SellController::class, 'create'])->name('sell.create');
    Route::post('/sell', [SellController::class, 'store'])->name('sell.store');

    // マイページ関連は /mypage に集約（page=buy / sell で分岐）
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');

    // プロフィール編集
    Route::get('/mypage/profile', [MypageController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [MypageController::class, 'update'])->name('profile.update');
});
