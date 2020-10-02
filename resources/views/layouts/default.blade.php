<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title>tabReborn | Version: {{ env('APP_VERSION') }}</title>
        <script src="{{ url('fontawesome.js') }}"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.0/css/bulma.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="{{ url('styles.css') }}" />
        <link rel="stylesheet" href="{{ url('switchery.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.3.2/main.min.css">
        <script src="{{ url('switchery.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.3.2/main.min.js"></script>
    </head>

    <body>
        
        @include('includes.navbar')

        <div class="container">

            <br>

            @yield('content')

            @include('includes.footer')

        </div>

    </body>

</html>