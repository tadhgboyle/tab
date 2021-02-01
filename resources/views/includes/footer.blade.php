@php
use App\Role;
use Illuminate\Database\Eloquent\Collection;
$greeting = Collection::make(['Welcome back', 'Greetings', 'Good day', 'Hello', 'Howdy', 'Bonjour', 'Hola', 'Long time no see',
'Salutations', 'Peek-a-boo', 'Ahoy', 'Top of the morning', 'G\'day'])->random();
@endphp
<hr>
<footer style="margin-bottom: 20px;">
    @auth
    <p>{{ $greeting }}, <b>{{ Auth::user()->full_name }}</b> ({{ Auth::user()->role->name }})</p>
    @endauth
    <p><i>tabReborn</i> | Version: {{ env('APP_VERSION') }}</p>
</footer>