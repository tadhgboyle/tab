<x-detail-card title="Timeline">
    <ol class="relative border-s border-gray-200 ml-1 py-2">
        @foreach($timeline as $entry)
        <li class="mb-2 ms-4">
            <div class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -start-1.5 border border-white"></div>
            <span class="mb-1 text-sm font-normal leading-none text-gray-600" title="{{ $entry->time->format('M jS Y h:ia') }}">
                {{ $entry->time->diffForHumans() }}
            </span>
            @if($entry->actor)
                <h3 class="text-sm font-semibold text-gray-900">{{ $entry->emoji }} {{ $entry->actor->full_name }}</h3>
            @endif
            <p class="text-sm font-normal text-gray-800">{{ !$entry->actor ? $entry->emoji : '' }}
                @if($entry->link)
                    <a href="{{ $entry->link }}" class="hover:underline">{{ $entry->description }}</a>
                @else
                    {{ $entry->description }}
                @endif
            </p>
        </li>
        @endforeach
    </ol>
</x-detail-card>
