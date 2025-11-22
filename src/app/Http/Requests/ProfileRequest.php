<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_image' => ['nullable', 'mimes:jpeg,png', 'max:5120'],
            'name'          => ['required', 'string', 'max:20'],
            'postal_code'   => ['required', 'string', 'regex:/^\d{3}-\d{4}$/'],
            'address'       => ['required', 'string', 'max:255'],
            'building'      => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_image.mimes' => 'プロフィール画像はJPEGまたはPNG形式を指定してください。',
            'profile_image.max'   => 'プロフィール画像のサイズは5MB以下にしてください。',
            'name.required'       => 'ユーザー名を入力してください',
            'name.max'            => 'ユーザー名は20文字以内で入力してください',
            'postal_code.required'=> '郵便番号を入力してください',
            'postal_code.regex'   => '郵便番号は「123-4567」の形式で入力してください',
            'address.required'    => '住所を入力してください',
            'address.max'         => '住所は255文字以内で入力してください',
            'building.max'        => '建物名は255文字以内で入力してください',
        ];
    }

    public function attributes(): array
    {
        return [
            'profile_image' => 'プロフィール画像',
            'name'          => 'ユーザー名',
            'postal_code'   => '郵便番号',
            'address'       => '住所',
            'building'      => '建物名',
        ];
    }
}