<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>tabReborn | Version: {{ env('APP_VERSION') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.0/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ url('styles.css') }}" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    
    <div class="container">

        @include('includes.navbar')

        <br>

        @yield('content')

        <hr>

        @include('includes.footer')

    </div>

    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

</body>

</html>