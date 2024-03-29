@if (count(\Laravel\Nova\Nova::availableResources(request())))
    @php
        $parents = [];

        $navigation->each(function ($item, $key) use (&$parents) {
            switch ($key) {
                case in_array($key, ['Games', 'User Info', 'Others', 'Tournaments', 'Types', 'Shop']):
                    $parents['Definitions'][$key] = $item;
                    break;

                case in_array($key, ['OAuth', 'Settings']):
                    $parents['Setup'][$key] = $item;
                    break;

                default:
                    $parents['Resources'][$key] = $item;
                    break;
            }
        });
    @endphp

    @foreach($parents as $title => $parent)
        @switch($title)
            @case('Definitions')
                <h3 class="flex items-center font-normal text-white mb-6 text-base no-underline">
                    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="2 2 20 20" width="20" height="20">
                        <path fill="var(--sidebar-icon)" d="M17 22a2 2 0 0 1-2-2v-1a1 1 0 0 0-1-1 1 1 0 0 0-1 1v1a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-3H5a3 3 0 1 1 0-6h1V8c0-1.11.9-2 2-2h3V5a3 3 0 1 1 6 0v1h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1a1 1 0 0 0-1 1 1 1 0 0 0 1 1h1a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3zm3-2v-3h-1a3 3 0 1 1 0-6h1V8h-3a2 2 0 0 1-2-2V5a1 1 0 0 0-1-1 1 1 0 0 0-1 1v1a2 2 0 0 1-2 2H8v3a2 2 0 0 1-2 2H5a1 1 0 0 0-1 1 1 1 0 0 0 1 1h1a2 2 0 0 1 2 2v3h3v-1a3 3 0 1 1 6 0v1h3z"/>
                    </svg>
                    <span class="sidebar-label">@lang($title)</span>
                </h3>
                @break

            @case('Setup')
                <h3 class="flex items-center font-normal text-white mb-6 text-base no-underline">
                    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="2 2 20 20" width="20" height="20">
                        <path fill="var(--sidebar-icon)" d="M9 4.58V4c0-1.1.9-2 2-2h2a2 2 0 0 1 2 2v.58a8 8 0 0 1 1.92 1.11l.5-.29a2 2 0 0 1 2.74.73l1 1.74a2 2 0 0 1-.73 2.73l-.5.29a8.06 8.06 0 0 1 0 2.22l.5.3a2 2 0 0 1 .73 2.72l-1 1.74a2 2 0 0 1-2.73.73l-.5-.3A8 8 0 0 1 15 19.43V20a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-.58a8 8 0 0 1-1.92-1.11l-.5.29a2 2 0 0 1-2.74-.73l-1-1.74a2 2 0 0 1 .73-2.73l.5-.29a8.06 8.06 0 0 1 0-2.22l-.5-.3a2 2 0 0 1-.73-2.72l1-1.74a2 2 0 0 1 2.73-.73l.5.3A8 8 0 0 1 9 4.57zM7.88 7.64l-.54.51-1.77-1.02-1 1.74 1.76 1.01-.17.73a6.02 6.02 0 0 0 0 2.78l.17.73-1.76 1.01 1 1.74 1.77-1.02.54.51a6 6 0 0 0 2.4 1.4l.72.2V20h2v-2.04l.71-.2a6 6 0 0 0 2.41-1.4l.54-.51 1.77 1.02 1-1.74-1.76-1.01.17-.73a6.02 6.02 0 0 0 0-2.78l-.17-.73 1.76-1.01-1-1.74-1.77 1.02-.54-.51a6 6 0 0 0-2.4-1.4l-.72-.2V4h-2v2.04l-.71.2a6 6 0 0 0-2.41 1.4zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
                    </svg>
                    <span class="sidebar-label">@lang($title)</span>
                </h3>
                @break

            @default
                <h3 class="flex items-center font-normal text-white mb-6 text-base no-underline">
                    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill="var(--sidebar-icon)" d="M3 1h4c1.1045695 0 2 .8954305 2 2v4c0 1.1045695-.8954305 2-2 2H3c-1.1045695 0-2-.8954305-2-2V3c0-1.1045695.8954305-2 2-2zm0 2v4h4V3H3zm10-2h4c1.1045695 0 2 .8954305 2 2v4c0 1.1045695-.8954305 2-2 2h-4c-1.1045695 0-2-.8954305-2-2V3c0-1.1045695.8954305-2 2-2zm0 2v4h4V3h-4zM3 11h4c1.1045695 0 2 .8954305 2 2v4c0 1.1045695-.8954305 2-2 2H3c-1.1045695 0-2-.8954305-2-2v-4c0-1.1045695.8954305-2 2-2zm0 2v4h4v-4H3zm10-2h4c1.1045695 0 2 .8954305 2 2v4c0 1.1045695-.8954305 2-2 2h-4c-1.1045695 0-2-.8954305-2-2v-4c0-1.1045695.8954305-2 2-2zm0 2v4h4v-4h-4z"
                        />
                    </svg>
                    <span class="sidebar-label">{{ __('Resources') }}</span>
                </h3>
        @endswitch

        @foreach($parent as $group => $resources)
            @if (count($groups) > 1)
                <h4 class="ml-8 mb-4 text-xs text-white-50% uppercase tracking-wide">{{ $group }}</h4>
            @endif

            <ul class="list-reset mb-8">
                @foreach($resources as $resource)
                    <li class="leading-tight mb-4 ml-8 text-sm">
                        <router-link :to="{
                            name: 'index',
                            params: {
                                resourceName: '{{ $resource::uriKey() }}'
                            }
                        }" class="text-white text-justify no-underline dim">
                            @if(\Illuminate\Support\Str::contains($resource::label(), 'Type'))
                                {{ trim(str_ireplace(['types', 'type'], null, $resource::label())) }}
                            @else
                                {{ $resource::label() }}
                            @endif
                        </router-link>
                    </li>
                @endforeach
            </ul>
        @endforeach
    @endforeach
@endif
