<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            body {
                font-size: 10px;
            }

            table {
                width: 100%;
                margin-bottom: 0.5rem;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 0.2rem;
                border: 1px solid #ddd;
            }

            .has-text-weight-bold {
                font-weight: bold;
            }

            .has-text-italic {
                font-style: italic;
            }

            .summary-table tr td:nth-child(2) {
                text-align: right;
            }

            .money-column {
                text-align: right;
            }
        </style>
    </head>

    <body>
        @yield('content')
    </body>
</html>
