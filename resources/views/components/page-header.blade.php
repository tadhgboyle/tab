<div class="lg:flex lg:items-center lg:justify-between mb-4">
    <h1 class="text-3xl font-bold">{{ $title }}</h1>
    @isset($actions)
        <div class="flex items-center space-x-2">
            @foreach($actions as $action)
                @if($action['can'])
                    @isset($action['href'])
                        <x-filament::button href="{{ $action['href'] }}" tag="a" color="{{ $action['color'] ?? 'primary' }}">
                            {{ $action['label'] }}
                        </x-filament::button>
                    @else
                        <x-filament::button onclick="{{ $action['onclick'] }}" color="{{ $action['color'] ?? 'primary' }}">
                            {{ $action['label'] }}
                        </x-filament::button>  
                    @endif
                @endif
            @endforeach
        </div>
    @endisset
</div>
