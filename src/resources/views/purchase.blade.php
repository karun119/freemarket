@extends('layouts.app')
@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase-container">
    {{-- 左カラム：商品情報・支払い選択・配送先 --}}
    <div class="purchase-left">

        {{-- 商品情報 --}}
        <div class="product-info">
            <img src="{{ asset('storage/' . $product->image_path) }}" 
                alt="{{ $product->item_name }}" 
                class="purchase-img">
            <div class="product-text">
                <p class="product-name">{{ $product->item_name }}</p>
                <p class="product-price">
                    <span>¥</span>{{ number_format($product->price) }}
                </p>
            </div>
        </div>

        <hr class="purchase-separator">

        {{-- 支払い方法 --}}
        @php
            $selectedPayment = session('payment_method.' . $product->id, '');
        @endphp
        <h3 class="section-title">お支払い方法</h3>
        <div class="payment-form">
            <select id="payment_method_left">
                <option value="" {{ $selectedPayment === '' ? 'selected' : '' }}>選択してください</option>
                <option value="convenience" {{ $selectedPayment === 'convenience' ? 'selected' : '' }}>コンビニ払い</option>
                <option value="card" {{ $selectedPayment === 'card' ? 'selected' : '' }}>カード払い</option>
            </select>
        </div>

        <hr class="purchase-separator">

        {{-- 配送先 --}}
        <div class="address-header">
            <h3 class="section-title">配送先</h3>
            <form action="{{ route('purchase.address.edit', $product->id) }}" method="get" style="display:inline">
                <input type="hidden" name="payment_method" id="payment_method_hidden" value="{{ $selectedPayment }}">
                <button type="submit" class="address-edit-btn">変更する</button>
            </form>
        </div>
        <div class="address-form">
            <p>〒{{ session('sending_postcode',$profile->postal_code ?? '未登録') }}</〒>
            </p>
            <p>{{ session('sending_address', $profile->address ?? '未登録') }} {{ session('sending_building', $profile->building ?? '') }}</p>
        </div>
    </div>

    {{-- 右カラム：購入フォーム（テーブル + 購入ボタン） --}}
    <div class="purchase-right">
        <form action="{{ route('purchase.store', $product->id) }}" method="post">
            @csrf
            <input type="hidden" name="payment_method" id="payment_method_right" value="{{ $selectedPayment }}">
            <input type="hidden" name="sending_postcode" value="{{ session('sending_postcode', $profile->postal_code ?? '') }}">
            <input type="hidden" name="sending_address" value="{{ session('sending_address', $profile->address ?? '') }}">
            <input type="hidden" name="sending_building" value="{{ session('sending_building', $profile->building ?? '') }}">

            <table class="summary">
                <tr>
                    <th class="sum-label">商品代金</th>
                    <td class="sum-value">¥{{ number_format($product->price) }}</td>
                </tr>
                <tr>
                    <th class="sum-label">支払い方法</th>
                    <td class="sum-value" id="summary_payment">
                        {{ $selectedPayment === 'convenience' ? 'コンビニ払い' 
                            : ($selectedPayment === 'card' ? 'カード払い' 
                            : '選択してください') }}
                    </td>
                </tr>
            </table>
            @error('payment_method')
                <p class="error-message">{{ $message }}</p>
            @enderror

            <button type="submit" class="purchase-btn">購入する</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
   document.addEventListener('DOMContentLoaded', function () {
    const paymentLeft = document.getElementById('payment_method_left');
    const paymentRight = document.getElementById('payment_method_right');
    const paymentHidden = document.getElementById('payment_method_hidden');
    const paymentSummary = document.getElementById('summary_payment');

    // 左カラムの選択が変わったら右カラム hidden と summary を更新
    paymentLeft.addEventListener('change', function () {
        const val = paymentLeft.value;
        paymentRight.value = val;   // 右フォーム hidden
        paymentHidden.value = val;  // 住所変更フォーム hidden

        paymentSummary.textContent = val === 'convenience' ? 'コンビニ払い'
            : val === 'card' ? 'カード払い' : '選択してください';
    });
});
</script>
@endsection
