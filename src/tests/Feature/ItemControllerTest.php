<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Condition;
use App\Models\Category;
use App\Models\Order;
use App\Models\Comment;
use App\Models\Profile;




class ItemControllerTest extends TestCase
{
   use RefreshDatabase;

   public function test_全商品を取得できる()
    {
            // テスト用ユーザー作成
        $user = User::factory()->create();

        // 商品配列
        $productsData = [
            ['腕時計', 'products/watch.jpg'],
            ['HDD', 'products/hdd.jpg'],
            ['革靴', 'products/shoes.jpg'],
        ];
        $condition = Condition::create([
            'name' => '良好'
        ]);

        // カテゴリ作成
        $category = Category::create([
            'category' => 'アクセサリー'
        ]);

        // まとめて商品作成
        foreach ($productsData as [$name, $image]) {
            $product = Product::create([
                'user_id' => $user->id,
                'condition_id' => $condition->id,  // ダミー値
                'item_name' => $name,
                'image_path' => $image,
                'price' => 1000,      // ダミー値
                'brand' => null,
                'description' => 'ダミー説明',
            ]);
            // 中間テーブルにカテゴリを付与
            $product->categories()->attach($category->id);
        }

        // 商品一覧ページにアクセス
        $response = $this->get('/');

        $response->assertStatus(200);

        // 商品名と画像が表示されているか確認
        foreach (Product::all() as $product) {
            $response->assertSee($product->item_name);
            $response->assertSee('storage/' . $product->image_path);
        }
    }

    public function test_購入済み商品は_sold_と表示される()
    {
        // 出品者ユーザー
        $seller = User::factory()->create();

        // 購入者ユーザー
        $buyer = User::factory()->create();

        // 商品の条件
        $condition = Condition::create(['name' => '良好']);

        // 商品カテゴリ
        $category = Category::create(['category' => 'アクセサリー']);

        // 商品作成
        $product = Product::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'item_name' => '腕時計',
            'image_path' => 'products/watch.jpg',
            'price' => 1000,
            'brand' => 'ブランドA',
            'description' => 'テスト商品',
        ]);

        // 中間テーブルにカテゴリ付与
        $product->categories()->attach($category->id);

        // 購入データ作成（購入済みにする）
        Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'sending_postcode' => '123-4567',
            'sending_address' => '東京都渋谷区',
            'sending_building' => 'ビル101',
            'payment_method' => 'カード',
        ]);
         // 商品ページにアクセス
        $response = $this->get('/');

        $response->assertStatus(200);

        // 商品名と Sold 表示を確認
        $response->assertSee($product->item_name);
        $response->assertSee('Sold');
    }

    public function test_自分が出品した商品は表示されない()
    {
        // 出品者（ログインユーザー）
        $user = User::factory()->create();

        // 他のユーザー
        $otherUser = User::factory()->create();

        // 条件とカテゴリ作成
        $condition = Condition::create(['name' => '良好']);
        $category = Category::create(['category' => '家電']);

        // 自分の商品
        $myProduct = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => '自分のノートPC',
            'image_path' => 'products/mypc.jpg',
            'price' => 50000,
            'brand' => '自作',
            'description' => '自分が出品した商品',
        ]);
        $myProduct->categories()->attach($category->id);

        // 他人の商品
        $otherProduct = Product::create([
            'user_id' => $otherUser->id,
            'condition_id' => $condition->id,
            'item_name' => '他人のスマホ',
            'image_path' => 'products/phone.jpg',
            'price' => 30000,
            'brand' => 'Apple',
            'description' => '他人が出品した商品',
        ]);
        $otherProduct->categories()->attach($category->id);

        // ログインして商品一覧ページにアクセス
        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);

        // 自分の商品は表示されない
        $response->assertDontSee($myProduct->item_name);

        // 他人の商品は表示される
        $response->assertSee($otherProduct->item_name);
    }


    public function test_いいねした商品だけが表示される()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $condition = Condition::create(['name' => '良好']);
        $category = Category::create(['category' => '家電']);

        // 他人の商品
        $product = Product::create([
            'user_id' => $otherUser->id,
            'condition_id' => $condition->id,
            'item_name' => 'テストスマホ',
            'image_path' => 'products/phone.jpg',
            'price' => 30000,
            'brand' => 'Apple',
            'description' => 'いいね対象商品',
        ]);
        $product->categories()->attach($category->id);

        // お気に入り登録
        $user->favoriteProducts()->attach($product->id);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee($product->item_name);
    }

    public function test_購入済み商品はSoldと表示される()
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();

        $condition = Condition::create(['name' => '良好']);
        $category = Category::create(['category' => 'アクセサリー']);

        // 商品作成
        $product = Product::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'item_name' => '購入済み時計',
            'image_path' => 'products/watch.jpg',
            'price' => 20000,
            'brand' => 'SEIKO',
            'description' => 'Sold対象商品',
        ]);
        $product->categories()->attach($category->id);

        // お気に入り登録
        $user->favoriteProducts()->attach($product->id);

        // 購入済みにする
        Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'sending_postcode' => '123-4567',
            'sending_address' => '東京都新宿区',
            'sending_building' => 'テストビル',
            'payment_method' => 'credit',
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('Sold');
        }

    public function test_未認証の場合は何も表示されない()
    {
        $response = $this->get('/?tab=mylist');

        $response->assertRedirect(route('login'));
    }

    public function test_商品名で部分一致検索ができる()
    {
        $user = User::factory()->create();
        $condition = Condition::create(['name' => '新品']);
        $category = Category::create(['category' => '家電']);

        // 商品を1つだけ作成
        $product = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => 'MacBook Pro',
            'image_path' => 'products/macbook.jpg',
            'price' => 200000,
            'brand' => 'Apple',
            'description' => 'テスト用商品',
        ]);
        $product->categories()->attach($category->id);

        // 「Mac」で検索
        $response = $this->get('/?keyword=Mac');

        $response->assertStatus(200);
        $response->assertSee('MacBook Pro');
    }

    public function test_検索状態がマイリストでも保持されている()
    {
        $user = User::factory()->create();
        $this->actingAs($user); // ログイン

        // ホームページで「Mac」を検索（商品がなくてもOK）
        $response = $this->get('/?keyword=Mac');
        $response->assertStatus(200);

        // マイリストタブに遷移（同じキーワードを保持しているか）
        $response = $this->get('/?tab=mylist&keyword=Mac');
        $response->assertStatus(200);

        // 検索キーワード「Mac」が引き継がれていることを確認
        $response->assertSee('Mac');
    }

    public function test_商品詳細ページに必要な情報が表示される()
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'image_path' => 'profiles/test.png',
            'postal_code' => '123-4567',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
        ]);

        $condition = Condition::create(['name' => '新品']);
        $category = Category::create(['category' => '家電']);

        // 商品作成
        $product = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => 'テスト商品',
            'image_path' => 'products/test.png',
            'price' => 10000,
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品です。',
        ]);
        $product->categories()->attach($category->id);

        // コメント作成
        $commentUser = User::factory()->create(['name' => 'コメントユーザー']);
        Profile::create([
            'user_id' => $commentUser->id,
            'image_path' => 'profiles/commenter.png',
            'postal_code' => '000-0000',
            'address' => 'テスト市テスト町',
            'building' => 'テストビル',
        ]);
        $product->comments()->create([
            'user_id' => $commentUser->id,
            'comment' => 'これはテストコメントです。',
        ]);

        // お気に入り（いいね）作成
        $product->favoritedByUsers()->attach($user->id);

        // アクセス
        $response = $this->get(route('item.show', $product->id));

        $response->assertStatus(200);

        // 商品の基本情報
        $response->assertSee('テスト商品');
        $response->assertSee('テストブランド');
        $response->assertSee('10,000');
        $response->assertSee('これはテスト用の商品です。');
        $response->assertSee('家電');
        $response->assertSee('新品');

        // コメント情報
        $response->assertSee('コメントユーザー');
        $response->assertSee('これはテストコメントです。');

        // プロフィール画像
        $response->assertSee('profiles/commenter.png');

        // いいね数とコメント数を厳密に検証
        $response->assertSeeInOrder([
            '<span class="product__favorite-count">1</span>',
            '<span class="product__comment-count">1</span>',
        ], false); // ← false で HTML をエスケープしない
    }
    
    public function test_複数カテゴリが商品詳細ページに表示される()
    {
        $user = User::factory()->create();
        $condition = Condition::create(['name' => '新品']);

        // カテゴリを複数作成
        $category1 = Category::create(['category' => '家電']);
        $category2 = Category::create(['category' => 'パソコン']);
        $category3 = Category::create(['category' => '周辺機器']);

        // 商品作成
        $product = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => 'テスト商品複数カテゴリ',
            'image_path' => 'products/test_multi.png',
            'price' => 50000,
            'brand' => 'テストブランド',
            'description' => '複数カテゴリの商品です。',
        ]);

        // 複数カテゴリを紐付け
        $product->categories()->attach([$category1->id, $category2->id, $category3->id]);

        // 商品詳細ページへアクセス
        $response = $this->get(route('item.show', $product->id));
        $response->assertStatus(200);

        // 複数カテゴリが表示されているか検証
        $response->assertSee('家電');
        $response->assertSee('パソコン');
        $response->assertSee('周辺機器');
    }

    public function test_いいねアイコンを押下することによって、いいねした商品として登録することができる。()
    {
         // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 商品の関連データを作成（固定値）
        $condition = Condition::create(['name' => '新品']);
        $category = Category::create(['category' => '家電']);

        // 3. 商品作成
        $product = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => 'テスト商品',
            'image_path' => 'products/test.png',
            'price' => 10000,
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品です。',
        ]);
        $product->categories()->attach($category->id);

        // 4. ログイン状態でいいねPOST
        $response = $this->actingAs($user)
            ->post(route('item.store', $product->id), [
                'action' => 'toggle_favorite',
            ]);

        // 5. 商品詳細ページにリダイレクトされること
        $response->assertRedirect(route('item.show', $product->id));

        // 6. favoritesテーブルに登録されていることを確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // 7. 商品詳細ページでいいね数が1になっていることを確認
        $this->actingAs($user)
            ->get(route('item.show', $product->id))
            ->assertSee('1');
    
    }

    public function test_追加済みのアイコンは色が変化する()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 商品関連データ作成
        $condition = Condition::create(['name' => '新品']);
        $category = Category::create(['category' => '家電']);

        $product = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => 'テスト商品',
            'image_path' => 'products/test.png',
            'price' => 10000,
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品です。',
        ]);
        $product->categories()->attach($category->id);

        // 3. favorites テーブルに既に登録（いいね済み状態）
        $product->favoritedByUsers()->attach($user->id);

        // 4. 商品詳細ページにログイン状態でアクセス
        $response = $this->actingAs($user)
            ->get(route('item.show', $product->id));

        $response->assertStatus(200);

        // 5. active クラスが存在するか確認
        $response->assertSee('class="star-icon active"', false);

        // 6. いいね数も確認
        $response->assertSee('1');
    }

    public function test_再度いいねアイコンを押下することによって、いいねを解除することができる()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. 商品関連データ作成
        $condition = Condition::create(['name' => '新品']);
        $category = Category::create(['category' => '家電']);

        $product = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => 'テスト商品',
            'image_path' => 'products/test.png',
            'price' => 10000,
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品です。',
        ]);
        $product->categories()->attach($category->id);

        // 3. まずいいね済みにする
        $product->favoritedByUsers()->attach($user->id);

        // 4. ログイン状態で再度いいね（解除）POST
        $response = $this->actingAs($user)
            ->post(route('item.store', $product->id), [
                'action' => 'toggle_favorite',
            ]);

        // 5. リダイレクト確認
        $response->assertRedirect(route('item.show', $product->id));

        // 6. favorites テーブルから削除されていることを確認
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // 7. 商品詳細ページを再取得 → いいね数が0になっている
        $this->actingAs($user)
            ->get(route('item.show', $product->id))
            ->assertSee((string)0);
    }

    public function test_ログイン済みのユーザーはコメントを送信できる()
    {
        // 1. ユーザー、商品、条件、カテゴリ作成
        $user = User::factory()->create();
        $condition = Condition::create(['name' => '新品']);
        $category = Category::create(['category' => '家電']);

        $product = Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'item_name' => 'テスト商品',
            'image_path' => 'products/test.png',
            'price' => 10000,
            'brand' => 'テストブランド',
            'description' => 'テスト用商品です。',
        ]);

        $product->categories()->attach($category->id);

        // 2. ログイン状態でコメント送信
        $this->actingAs($user)
            ->post(route('item.store', $product->id), [
                'action' => 'comment',
                'comment' => 'これはテストコメントです。',
            ])
            ->assertRedirect(route('item.show', $product->id));

        // 3. commentsテーブルに保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => 'これはテストコメントです。',
        ]);

        // 4. 商品詳細ページでコメントが表示されるか確認
        $response = $this->actingAs($user)->get(route('item.show', $product->id));
        $response->assertSee('これはテストコメントです.');
    }


}
