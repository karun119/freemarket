@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    <!-- プロフィールエリア -->
    <div class="mypage__profile">
        <div class="mypage__profile-left">
            @if ($profile && $profile->image_path)
                <div class="mypage__profile-image-circle">
            <img src="{{ asset('storage/' . $profile->image_path) }}" alt="プロフィール画像" class="mypage__profile-image">
        </div>
            @else
                <div class="mypage__profile-image--default"></div>
            @endif
        </div>

        <div class="mypage__profile-center">
            <h2 class="mypage__username">{{ $user->name }}</h2>
        </div>

        <div class="mypage__profile-right">
            <form action="{{ route('profile.edit') }}" method="get">
                <button type="submit" class="mypage__edit-button">プロフィール編集</button>
            </form>
        </div>
    </div>

    <!-- タブ -->
    <div class="mypage__tabs">
        <a href="{{ route('mypage.index', ['page' => 'sell']) }}" class="mypage__tab {{ $viewType === 'sell' ? 'active' : '' }}">
            出品した商品
        </a>
        <a href="{{ route('mypage.index', ['page' => 'buy']) }}" class="mypage__tab {{ $viewType === 'buy' ? 'active' : '' }}">
            購入した商品
        </a>
    </div>

    <!-- タブ下ボーダー -->
    <div class="mypage__tabs-border"></div>

    <!-- 商品一覧 -->
    @if ($viewType === 'buy')
    <!-- 購入商品一覧 -->
    <div class="product-list">
        @forelse ($orders as $order)
            @php $product = $order->product; @endphp
            <div class="product-card">
                <div class="product-card__image-wrapper">
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->item_name }}" class="product-card__image">
                    <!-- @if($product->is_sold)
                        <span class="product-card__sold">Sold</span>
                    @endif -->
                </div>
                <div class="product-card__name">
                    {{ $product->item_name }}
                </div>
            </div>
        @empty
        <p class="no-products">商品がありません。</p>
        @endforelse
    </div>

@else
    <!-- 出品商品一覧 -->
    <div class="mypage__product-list">
        @forelse ($products as $product)
            <div class="product-card">
                <div class="product-card__image-wrapper">
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->item_name }}" class="product-card__image">
                </div>
                <div class="product-card__name">
                    {{ $product->item_name }}
                </div>
            </div>
        @empty
        <p class="no-products">商品がありません。</p>
        @endforelse
    </div>
@endif


</div>
@endsection
