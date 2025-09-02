<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_method' => ['required', 'in:convenience,card'],
            'sending_postcode' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'sending_address' => ['required', 'max:255'],
            'sending_building' => ['nullable', 'max:255'],
        ];
    }
     public function messages()
    {
        return [
            'payment_method.required'   => '支払い方法を選択してください。',
            'payment_method.in'         => '支払い方法はコンビニ払いかカード払いを選択してください。',
            'sending_postcode.required' => '郵便番号は必須です。',
            'sending_postcode.regex'    => '郵便番号は「123-4567」の形式で入力してください。',
            'sending_address.required'  => '住所は必須です。',
            'sending_address.max'       => '住所は255文字以内で入力してください。',
            'sending_building.max'      => '建物名は255文字以内で入力してください。',
        ];
    }
}
