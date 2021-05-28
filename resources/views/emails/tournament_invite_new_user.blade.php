@component('mail::message')
# You've been invited!

{{ $username }} {{ $organization ? 'from ' . $organization->title : '' }} is inviting you to join the {{ $tournament->title }} tournament.<br>
To join download lair.gg's application from:<br>

@component('mail::double_buttons', ['url' => 'https://lair.gg,https://lair.gg'])
iOS App,Android App
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
