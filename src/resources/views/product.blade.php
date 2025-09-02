@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/product.css') }}">
@endsection

@section('content')
<div class="product-container">
    <div class="product__main">
        <img src="{{ asset('storage/' . $product->image_path) }}" alt="商品画像" class="detail__image">

        <div class="product__info">
            <h1 class="product__title">{{ $product->item_name }}</h1>
            <p class="product__brand">{{ $product->brand }}</p>
            <p class="product__price">
                <span class="price">¥{{ number_format($product->price) }}</span>
                <span class="tax-included">(税込)</span>
            </p>

            <div class="product__icons">
                <div class="product__icon-wrap">
                    <form method="POST" action="{{ route('item.store', $product->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="toggle_favorite">
                        <button type="submit" class="product__icon--favorite">
                            <x-icon.star :active="auth()->check() && $product->favoritedByUsers->contains(auth()->id())" />
                            <span class="fallback-text">お気に入りに追加</span>
                        </button>
                    </form>
                    <span class="product__favorite-count">{{ $product->favoritedByUsers->count() }}</span>
                </div>
                <div class="product__icon-wrap">
                    <img src="{{ asset('images/comment.svg') }}" class="product__icon-comment" alt="コメント">
                    <span class="product__comment-count">{{ $product->comments->count() }}</span>
                </div>
            </div>

            <form method="GET" action="{{ route('purchase.show', $product->id) }}">
                <input type="submit" value="購入手続きへ" class="product__btn">
            </form>

            <!-- ここから商品説明、詳細、コメントを右カラム内に配置 -->
            <p class="product__section-title">商品説明</p>
            <p class="product__description">{{ $product->description }}</p>

            <p class="product__section-title">商品の情報</p>
            <div class="product__details">
                <div class="product__detail">
                    <label>カテゴリー</label>
                    <span class="product__categories">
                        @foreach ($product->categories as $category)
                            <span class="product__category">{{ $category->category }}</span>
                        @endforeach
                    </span>
                </div>
                <div class="product__detail">
                    <label>商品の状態</label>
                    <span>{{ $product->condition->name }}</span>
                </div>
                <div class="product__detail">
                    <label class="label-comment">コメント ({{ $product->comments->count() }})</label>
                </div>
            </div>

            <div class="product__comments">
                @foreach ($product->comments as $comment)
                <div class="product__comment">
                    <div class="product__comment-header">
                        <div class="product__user-icon">
                           @php $profile = $comment->user?->profile; @endphp
                            @if ($profile && $profile->image_path)
                                <div class="product__user-icon-circle">
                                    <img src="{{ asset('storage/' . $profile->image_path) }}" alt="ユーザーアイコン" class="product__user-icon-image">
                                </div>
                            @else
                                <div class="product__user-icon--default"></div>
                            @endif
                        </div>
                        <span class="product__username">{{ $comment->user?->name ?? '名無し' }}</span>
                    </div>
                    <p class="product__comment-text">{{ $comment->comment }}</p>
                </div>
                @endforeach
            </div>

            <!-- コメント入力フォーム -->
            <p class="product__section-title-comment">商品へのコメント</p>
            <form method="POST" action="{{ route('item.store', $product->id) }}">
                @csrf
                <input type="hidden" name="action" value="comment">
                <textarea name="comment" class="product__comment-input" placeholder="">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="error-message">{{ $message }}</p>
                @enderror
                <input type="submit" value="コメントを送信する" class="product__btn">
            </form>
        </div> <!-- .product__info 終了 -->
    </div> <!-- .product__main 終了 -->
</div>
@endsection


@section('js')
<script>

document.addEventListener('DOMContentLoaded', function () {
    const favoriteButton = document.querySelector('.product__icon--favorite');
    const star = favoriteButton.querySelector('svg');
    const fallback = favoriteButton.querySelector('.fallback-text');

    // クリックでスターの色を切り替え
    favoriteButton.addEventListener('click', function () {
        star.classList.toggle('active');
    });

    // SVGが正しく表示されなかった場合、文字を表示
    if (!star || star.getBBox().width === 0) {
        fallback.style.display = 'inline';
    }
    // コメント入力欄の高さ自動調整
    const textarea = document.querySelector('.product__comment-input');
    if (textarea) {
        textarea.style.height = 'auto'; // 初期リセット

        textarea.addEventListener('input', function () {
            this.style.height = 'auto'; // 一旦リセット
            this.style.height = this.scrollHeight + 'px'; // 内容に合わせて高さ設定
        });
    }
});
</script>
@endsection

