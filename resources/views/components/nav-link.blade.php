@php
    $active = request()->routeIs(collect(isset($sublinks) ? $sublinks : [])->map(fn($s) => $s['route'])->push(...$routes));
@endphp

<li>
    <a href="{{ $url }}" class="flex items-center p-2 text-gray-900 rounded-lg group {{ $active ? 'bg-gray-100' : 'hover:bg-gray-100' }}">
        <span>{{ $icon }}</span>
        <span class="ms-3">{{ $name }}</span>
    </a>

    @if($active && isset($sublinks))
        <ul class="ps-8 mt-2 space-y-1">
            @foreach($sublinks as $sublink)
                <li>
                    <a href="{{ route($sublink['route']) }}" class="flex items-center p-1 text-gray-600 text-sm rounded-lg group {{ request()->routeIs(collect($sublink['route'])->push(...($sublink['sub_routes'] ?? []))) ? 'bg-gray-100' : 'hover:bg-gray-100' }}">
                        <span class="ms-3">{{ $sublink['name'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</li>