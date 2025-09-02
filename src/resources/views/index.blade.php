@extends('layouts.app')
@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endsection

@section('content')
<div class="product-index">
    @if(session('success'))
    <div class="flash-message flash-success">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flash-message flash-error">
        {{ session('error') }}
    </div>
    @endif

        <!-- タブ -->
        <div class="product-index__tabs">
            <a href="{{ url('/') }}?tab=recommend{{ request('keyword') ? '&keyword=' . urlencode(request('keyword')) : '' }}"
            class="product-index__tab {{ $tab === 'recommend' ? 'active' : '' }}">
                おすすめ
            </a>
            <a href="{{ url('/') }}?tab=mylist{{ request('keyword') ? '&keyword=' . urlencode(request('keyword')) : '' }}"
            class="product-index__tab {{ $tab === 'mylist' ? 'active' : '' }}">
                マイリスト
            </a>
</div>
    <!-- ボーダー -->
    <div class="product-index__tabs-border"></div>

<!-- 商品一覧 -->
    <div class="product-list">
        @forelse ($products as $product)
            <div class="product-card">
                <div class="product-card__image-wrapper">
                    <a href="{{ route('item.show', ['item' => $product->id]) }}">
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->item_name }}" class="product-card__image">
                    </a>
                    @if($product->is_sold)
                        <span class="product-card__sold">Sold</span>
                    @endif
                </div>
                <div class="product-card__name" >
                    <a href="{{ route('item.show', ['item' => $product->id]) }}">
                    {{ $product->item_name }}
                    </a>
                </div>
            </div>
        @empty
            <p class="no-products">商品がありません。</p>
        @endforelse
    </div>
</div>
@endsection