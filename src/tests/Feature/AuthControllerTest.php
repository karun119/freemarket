<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録時に名前が入力されていない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);

        $response->assertRedirect('/register');
    }

    public function test_会員登録時にメールアドレスが入力されていない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        $response->assertRedirect('/register');
    }

    public function test_会員登録時にパスワードが入力されていない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        $response->assertRedirect('/register');
    }

    public function test_会員登録時にパスワードが7文字以下の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);

        $response->assertRedirect('/register');
    }

    public function test_会員登録時にパスワード確認と一致しない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);

        $response->assertRedirect('/register');
    }

    public function test_全ての項目が入力されている場合、会員情報が登録され、プロフィール設定画面に遷移される()
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'name' => 'テストユーザー',
        ]);

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_会員登録後に認証メールが送信される()
        {
            Notification::fake();

            $response = $this->post('/register', [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            // 認証通知が送られたか確認
            $user = User::where('email', 'test@example.com')->first();
            Notification::assertSentTo($user, VerifyEmail::class);

            $response->assertRedirect(route('verification.notice'));
        }

    public function test_メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 一時的にログイン状態にする
        $this->actingAs($user);

        // 誘導画面を表示
        $response = $this->get(route('verification.notice'));
        $response->assertStatus(200);

        // メール認証リンクを生成
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 「認証はこちらから」ボタンを押す代わりにリンクへアクセス
        $response = $this->get($verifyUrl);

        // 認証サイト（プロフィール編集ページ）へリダイレクトすることを確認
        $response->assertRedirect(route('profile.edit'));
    }

    public function test_メール認証サイトのメール認証を完了すると、プロフィール編集画面に遷移する()
    {

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertRedirect('/mypage/profile');
    }

    public function test_ログイン時にメールアドレスが入力されていない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/login')->post('/login', [
        'email' => '',
        'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_ログイン時にパスワードが入力されていない場合、バリデーションメッセージが表示される()
    {   
        $response = $this->from('/login')->post('/login', [
        'email' => 'test@example.com',
        'password' => '',
        ]);

        $response->assertSessionHasErrors([
        'password' => 'パスワードを入力してください',
        ]);

        $response->assertRedirect('/login');
        }

    public function test_ログイン時入力情報が間違っている場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
        'email' => 'ログイン情報が登録されていません',
        ]);

        // 元のページにリダイレクト
        $response->assertRedirect('/login');
    }

    public function test_正しい情報が入力された場合、ログイン処理が実行される()
    {
        // 事前にユーザーを作成
        $user = User::factory()->create([
            'password' => bcrypt('password123'), // パスワードはハッシュ化
            'email_verified_at' => now(),        // メール認証済み
        ]);

        // ログイン処理
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // ログイン済みか確認
        $this->assertAuthenticatedAs($user);

        // intended にリダイレクトされることを確認
        $response->assertRedirect('/?tab=mylist');
    }

    public function test_ログアウトができる()
{
    // 1. テストユーザーを作成
    $user = User::factory()->create();

    // 2. ログイン状態にする
    $this->actingAs($user);

    // 3. POSTでログアウト処理を実行
    $response = $this->post('/logout');

    // 4. ログアウトされていることを確認
    $this->assertGuest();

    // 5. トップページにリダイレクトされることを確認
    $response->assertRedirect('/');
}



}
