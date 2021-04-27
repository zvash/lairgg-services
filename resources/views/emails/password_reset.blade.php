@component('mail::message')
# Confirm your email address

Your password reset token is: {{ $code }}

{{ config('app.name') }}
@endcomponent
