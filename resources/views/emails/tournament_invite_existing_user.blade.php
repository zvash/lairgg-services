@component('mail::message')
# You've been invited!

{{ $username }} {{ $organization ? 'from ' . $organization->title : '' }} is inviting you to join the {{ $tournament->title }} tournament.<br>
Check out your invitations.<br>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
