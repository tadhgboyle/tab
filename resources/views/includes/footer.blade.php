@php
use App\Roles;
$greetings = ['Welcome back', 'Greetings', 'Good day', 'Hello', 'Howdy', 'Bonjour', 'Hola', 'Long time no see',
'Salutations', 'Peek-a-boo', 'Ahoy', 'Top of the morning', 'G\'day'];
@endphp
<hr>
<footer>
    @auth
        <p>{{ $greetings[rand(0, count($greetings) - 1)] }}, <b>{{ Auth::user()->full_name }}</b> ({{ Roles::idToName(Auth::user()->role) }})</p>
    @endauth
    <p><i>tabReborn</i> | Version: {{ env('APP_VERSION') }}</p>
</footer>