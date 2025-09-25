<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Product;
use App\Models\Order;


class MypageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザー情報を取得できる()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $product = Product::factory()->create(['user_id' => $user->id]);

        $seller = User::factory()->create();
        $purchasedProduct = Product::factory()->create(['user_id' => $seller->id]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $purchasedProduct->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/mypage?page=sell');
        $response->assertStatus(200);
        $response->assertSee($product->item_name);
        $response->assertSee($user->name);
        $response->assertSee('storage/dummy.png');


        $response = $this->get('/mypage?page=buy');
        $response->assertStatus(200);
        $response->assertSee($purchasedProduct->item_name);
        $response->assertSee('storage/dummy.png');
    }


    public function test_プロフィール編集画面に初期値が表示される()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'image_path' => 'profile_images/test.png',
        ]);

        $this->actingAs($user);

        $response = $this->get('/mypage/profile');
        $response->assertStatus(200);

        $response->assertSee('テスト太郎');
        $response->assertSee('123-4567');
        $response->assertSee('東京都渋谷区');
        $response->assertSee('テストビル101');
        $response->assertSee('storage/' . $profile->image_path);
    }

}
