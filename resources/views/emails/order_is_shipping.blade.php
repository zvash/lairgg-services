@component('mail::message')
# Your order is shipping

Dear {{ $username }}
Your "{{ $order->product->title }}" is getting shipped to {{ $order->address }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
