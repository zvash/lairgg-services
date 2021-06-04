@component('mail::message')
# You've been invited!

{{ $username }} is inviting you to join the {{ $team->title }} team.<br>
Check out your invitations.<br>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
