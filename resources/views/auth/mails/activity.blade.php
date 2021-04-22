@component('mail::message')
{{ __('emails.activity.greeting', ['firstname' => $notifiable->first_name]) }}<br>
{{ __('emails.activity.topic', compact('action')) }}<br>
* {{ $location->country }}
* {{ $location->ip }}
<br>

@component('mail::button', ['url' => 'https://virtuleap.com/contact'])
    {{ __('emails.activity.button') }}
@endcomponent

{{ __('emails.activity.signature') }}<br><br>

{{ __('emails.activity.thanks') }}<br>
{{ config('app.name') }}
@endcomponent
