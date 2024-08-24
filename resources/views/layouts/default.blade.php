<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tab | {{ ucfirst($page) }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    @auth
        @impersonating
            <div class="has-text-centered has-background-light py-2">
                <p>🕵 You are impersonating {{ auth()->user()->full_name }}, <a href="{{ route('impersonate.leave') }}">click here to exit</a></p>
            </div>
        @endImpersonating

        @include('includes.navbar')
    @endauth

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
