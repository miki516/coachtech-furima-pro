@component('mail::message')
    # 取引完了のお知らせ

    {{ $order->product->name }} の取引が
    購入者 {{ $order->buyer->name }} さんにより「取引完了」となりました。

    - 取引ID: {{ $order->id }}
    - 商品名: {{ $order->product->name }}
    - 価格: ¥{{ number_format($order->product->price) }}
    - 取引画面URL: {{ route('trade.chat', ['order' => $order->id]) }}
@endcomponent
