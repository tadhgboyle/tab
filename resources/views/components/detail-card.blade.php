<div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
    <div class="px-3 py-3 border-b">
        <div class="text-sm font-semibold text-gray-950">
            {{ $title }}
        </div>
        @isset($subtitle)
            <div class="text-xs text-gray-500">
                {{ $subtitle }}
            </div>
        @endisset
    </div>
    <div class="bg-white px-3 text-gray-950 rounded-lg">
        {{ $slot }}
    </div>
</div>
