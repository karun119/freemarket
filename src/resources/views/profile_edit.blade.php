@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile_edit.css') }}">
@endsection

@section('content')
<div class="profile">
    <h1 class="profile__title">プロフィール設定</h1>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="profile__image-section">
            <div class="profile__image-circle">
                @if($profile && $profile->image_path)
                    <img src="{{ asset('storage/' . $profile->image_path) }}" alt="プロフィール画像" id="profileImage">
                @else
                    <img src="" alt="" id="profileImage" style="display:none;">
                @endif
            </div>

            <label class="profile__upload">
                画像を選択する
                <input type="file" name="profile_image" accept="image/*" id="profileImageInput" hidden>
            </label>
        </div>
        @error('profile_image')
                <p class="profile__error">{{ $message }}</p>
        @enderror
        

        <div class="profile__group">
            <label for="name">ユーザー名</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}">
            @error('name')
                <p class="profile__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile__group">
            <label for="postal_code">郵便番号</label>
            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $profile->postal_code) }}">
            @error('postal_code')
                <p class="profile__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile__group">
            <label for="address">住所</label>
            <input type="text" id="address" name="address" value="{{ old('address', $profile->address) }}">
            @error('address')
                <p class="profile__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="profile__group">
            <label for="building">建物名</label>
            <input type="text" id="building" name="building" value="{{ old('building', $profile->building) }}">
        </div>

        <div class="profile__submit-container">
            <input type="submit" value="更新する" class="profile__submit">
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const postalInput = document.getElementById('postal_code');

        postalInput.addEventListener('input', function (e) {
            let value = e.target.value;

            // 全角数字を半角数字に変換
            value = value.replace(/[０-９]/g, function(s) {
                return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
            });

            // 数字以外を除去し、7桁までに制限
            value = value.replace(/[^\d]/g, '').slice(0, 7);

            // ハイフン付与
            if (value.length >= 4) {
                e.target.value = value.slice(0, 3) + '-' + value.slice(3);
            } else {
                e.target.value = value;
            }
        });
        const input = document.getElementById('profileImageInput');
        const img = document.getElementById('profileImage');

        input.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file) return;

            img.src = URL.createObjectURL(file);
            img.style.display = 'block'; // 空の画像だった場合も表示
        });
    });
</script>
@endsection
