<?php 
use Illuminate\Support\Facades\Auth;
?>
<br>
@if(Auth::check())
<p>Logged in as: <b>{{ Auth::user()->username }}</b> [{{ Auth::user()->id }}] ({{ ucfirst(Auth::user()->role) }}). Click <a href="logout">here</a> to logout.</p>
@endif
<p><i>tabReborn</i> Version: {{ env('APP_VERSION') }}.</p>