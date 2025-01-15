<a href="{{ $route }}" 
   class="block px-4 my-3 py-2 text-sm font-medium text-gray-950 border {{ isset($active) && $active ? 'bg-gray-100 border-gray-200' : 'border-white' }} hover:bg-gray-100 rounded-md">
    {{ $slot }}
</a>
