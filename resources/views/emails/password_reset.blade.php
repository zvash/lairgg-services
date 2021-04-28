@component('mail::message')
# Password Reset Token

@component('mail::panel')
Your password reset token is: {{ $code }}
@endcomponent

{{ config('app.name') }}
@endcomponent
