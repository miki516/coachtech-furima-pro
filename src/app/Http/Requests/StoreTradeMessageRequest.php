<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTradeMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:400'],
            'image'   => ['nullable', 'file', 'mimes:jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            // 本文
            'message.required' => '本文を入力してください',
            'message.max'      => '本文は400文字以内で入力してください',

            // 画像
            'image.mimes'      => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }

        /**
         * バリデーションエラー時の戻り先 URL をカスタマイズ
         */
        protected function getRedirectUrl()
        {
            // ルートパラメータ {order} を取得
            $order = $this->route('order');

            // チャット画面のフォーム位置 (#trade-form) へ戻す
            return route('trade.chat', ['order' => $order]) . '#trade-form';
        }
}
