<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tab | {{ ucfirst($page) }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('favicon.ico') }}">

    <link rel="stylesheet" href="{{ url('css/bulma-0.9.2.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/dataTables-1.10.21.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/styles.css') }}" />
    <link rel="stylesheet" href="{{ url('css/switchery-0.8.2.css') }}">
    <link rel="stylesheet" href="{{ url('css/fullcalender-5.5.1.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/flatpickr-4.6.9.min.css') }}">

    <script src="{{ url('js/fa-5.15.2-all.min.js') }}"></script>
    <script src="{{ url('js/switchery-0.8.2.js') }}"></script>
    <script src="{{ url('js/flatpickr-4.6.9.min.js') }}"></script>
    <script src="{{ url('js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ url('js/jquery-ui-1.12.1.min.js') }}"></script>
    <script src="{{ url('js/jquery.dataTables-1.10.24.min.js') }}"></script>
    <script src="{{ url('js/fullcalendar-5.3.2.min.js') }}"></script>
</head>

<body>

    @include('includes.navbar')

    <div class="container">

        <br>

        @yield('content')

        @include('includes.footer')

    </div>

    <script>
        // initialize switches
        const switches = document.getElementsByClassName("js-switch");
        for (let i = 0; i < switches.length; i++) {
            new Switchery(switches.item(i), {
                color: '#48C774',
                secondaryColor: '#F56D71'
            });
        }

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
