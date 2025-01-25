<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>tab | {{ ucfirst($page) }}</title>

        <link rel="stylesheet" href="{{ url('css/styles.css') }}" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" />

        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @filamentScripts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="antialiased">
        <div class="flex flex-col h-screen justify-between">
            @include('includes.navbar')

            <div class="sm:ml-64">
                @auth
                <div class="bg-gray-50 border-b">
                    @impersonating
                    <div class="text-center text-sm py-2">
                        <p>🕵️ You're impersonating {{ auth()->user()->full_name }}, 
                            <a href="{{ route('impersonate.leave') }}" class="text-blue-600 hover:underline">click here to exit</a>
                        </p>
                    </div>
                    @endImpersonating
                
                    @if(auth()->user()->role->staff && auth()->user()->family)
                    <div class="text-center text-sm py-2">
                        <p>
                            @if(\Str::contains(request()->url(), '/admin'))
                                🏛 You're in an admin context, 
                                <a href="{{ route('family_view', auth()->user()->family) }}" class="text-blue-600 hover:underline">click here to view your family</a>
                            @else
                                🧑‍💼️ You're in a family context
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
                @endauth

                <div class="p-4">
                    @yield('content')
                </div>
            </div>
        </div>

        @livewire('notifications')

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
