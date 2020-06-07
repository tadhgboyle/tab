@php
use Illuminate\Support\Facades\Auth;
$greetings = ['Welcome back', 'Greetings', 'Good day', 'Hello', 'Howdy', 'Bonjour', 'Hola', 'Long time no see',
'Salutations', 'Peek-a-boo', 'Ahoy', 'Top of the morning'];
@endphp
<br>
@auth
<p><span title="Enjoy random greetings on every page!">{{ $greetings[rand(0, count($greetings) - 1)] }}</span>, <b>{{ Auth::user()->username }}</b>
    ({{ ucfirst(Auth::user()->role) }}). Click <a href="logout">here</a> to logout.</p>
@endauth
<p><i>tabReborn</i> | Version: {{ env('APP_VERSION') }}</p>