<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tab | {{ ucfirst($page) }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('favicon.ico') }}">

    <link rel="stylesheet" href="{{ url('css/app.css') }}">
    <link rel="stylesheet" href="{{ url('css/dataTables-1.10.21.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/styles.css') }}" />

    <script src="{{ url('js/app.js') }}"></script>
</head>

<body>

    @include('includes.navbar')

    <div class="container">

        <br>

        @yield('content')

        @include('includes.footer')

    </div>

    <script>
        // close modals on esc key press
        const modals = document.getElementsByClassName('modal');
        for (const m of modals) {
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    m.classList.remove("is-active");
                }
            });
        }

        // handle formatting money inputs
        const inputs = document.getElementsByClassName('money-input');
        for (const i of inputs) {
            i.onchange = function() {
                if (this.value && this.value.indexOf('.') === -1) {
                    this.value += '.00';
                }
            };
        }
    </script>
</body>

</html>
