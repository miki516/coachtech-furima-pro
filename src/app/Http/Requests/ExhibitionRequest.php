<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'image'       => ['required', 'mimes:jpeg,png', 'max:5120'],
            'category_id'   => ['required', 'array'],
            'category_id.*' => ['integer', 'exists:categories,id'],
            'condition'   => ['required', 'string'],
            'price'       => ['required', 'integer', 'min:0'],
            'brand'       => ['nullable', 'string', 'max:255'],
        ];
    }


    public function messages(): array
    {
        return [
            'image.mimes' => '商品画像はJPEGまたはPNG形式でアップロードしてください',
            'image.max' => '画像サイズは5MB以下にしてください',
            'image.required' => '商品画像を選択してください',
            'price.min' => '価格は0円以上で入力してください',
            'category_id.exists' => '選択したカテゴリーが存在しません',
            'category_id.required' => '商品のカテゴリーを選択してください',
            'condition.required' => '商品の状態を選択してください',
            'name.required' => '商品名を入力してください',
            'name.max' => '商品名は255文字以内で入力してください',
            'description.required' => '商品の説明を入力してください',
            'description.max' => '商品の説明は255文字以内で入力してください',
            'price.required' => '販売価格を入力してください',
            'price.integer' => '販売価格は整数で入力してください',
            'brand.max' => 'ブランド名は255文字以内で入力してください',
        ];
    }
}
