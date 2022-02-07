<hr>
<footer style="margin-bottom: 20px;">
    @auth
    <p>{{ Arr::random(['Welcome back', 'Greetings', 'Good day', 'Hello', 'Howdy', 'Bonjour', 'Hola', 'Long time no see', 'Salutations', 'Peek-a-boo', 'Ahoy', 'Top of the morning', 'G\'day']) }}, <b>{{ auth()->user()->full_name }}</b> ({{ auth()->user()->role->name }})</p>
    @endauth
    <p><i>tab</i> | Version: {{ env('APP_VERSION') }}</p>
</footer>
