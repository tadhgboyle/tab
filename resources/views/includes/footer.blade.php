@auth
    <hr>
    <footer style="margin-bottom: 20px;">
        <p>{{ Arr::random(\App\Providers\AppServiceProvider::EMOJIS) }} {{ Arr::random(\App\Providers\AppServiceProvider::GREETINGS) }}, <b>{{ auth()->user()->full_name }}</b> ({{ auth()->user()->role->name }})</p>
    </footer>
@endauth

