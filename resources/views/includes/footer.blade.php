@php
use Illuminate\Support\Facades\Auth;
$greetings = ['Welcome back', 'Greetings', 'Good day', 'Hello', 'Howdy', 'Bonjour'];
@endphp
<br>
@auth
<p>{{ $greetings[rand(0, count($greetings) - 1)] }}, <b>{{ Auth::user()->username }}</b>
    ({{ ucfirst(Auth::user()->role) }}). Click <a href="logout">here</a> to logout.</p>
@endauth
<p><i>tabReborn</i> | Version: {{ env('APP_VERSION') }}</p>