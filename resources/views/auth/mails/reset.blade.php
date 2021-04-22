@component('mail::message')
{{ __('emails.reset.greeting', ['firstname' => ucwords($notifiable->first_name)]) }}<br>
{{ __('emails.reset.topic') }}

@component('mail::button', ['url' => $url])
{{ __('emails.reset.button') }}
@endcomponent

{{ __('emails.reset.signature', ['count' => $expire]) }}<br>
{{ __('emails.reset.further') }}<br><br>

{{ __('emails.reset.thanks') }}<br>
{{ config('app.name') }}
@endcomponent
