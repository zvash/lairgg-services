@component('mail::message')
# You've been invited!

{{ $username }} is inviting you to join {{ $team->title }} team.<br>
To join download lair.gg's application from:<br>

@component('mail::double_buttons', ['url' => 'https://lair.gg,https://lair.gg'])
iOS App,Android App
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
