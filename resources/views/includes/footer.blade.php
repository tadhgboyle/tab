<?php 
use Illuminate\Support\Facades\Auth;
?>
<br>
@auth
<p>Logged in as: <b>{{ Auth::user()->username }}</b> [{{ Auth::user()->id }}] ({{ ucfirst(Auth::user()->role) }}). Click <a href="logout">here</a> to logout.</p>
@endauth
<p><i>tabReborn</i> | Version: {{ env('APP_VERSION') }}</p>