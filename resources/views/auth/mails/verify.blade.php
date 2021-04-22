@component('mail::message')
{{ __('emails.verification.greeting', ['firstname' => ucwords($notifiable->first_name)]) }}<br>
{{ __('emails.verification.topic') }}

@component('mail::button', ['url' => $url])
{{ __('emails.verification.button') }}
@endcomponent

{{ __('emails.verification.signature') }}<br>

{{ __('emails.verification.thanks') }}<br>
{{ config('app.name') }}
@endcomponent