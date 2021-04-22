@component('mail::message')
# Confirm your email address

To activate your account on lair.gg click on this button:

@component('mail::button', ['url' => 'https://lair.gg'])
Activate
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
