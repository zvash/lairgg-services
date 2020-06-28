@inject('storage', 'Illuminate\Contracts\Filesystem\Factory')

<dropdown-trigger class="h-9 flex items-center">
    @if($user->avatar && $storage->disk('s3')->exists($user->avatar))
        <img
            src="{{ $storage->disk('s3')->url($user->avatar) }}"
            class="rounded w-8 h-8 mr-3"
            alt="{{ $user->full_name }}"
        />
    @endisset

    <span class="text-90">
        {{ $user->full_name ?? $user->username }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    <ul class="list-reset">
        <li>
            <a href="{{ route('nova.logout') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </a>
        </li>
    </ul>
</dropdown-menu>
