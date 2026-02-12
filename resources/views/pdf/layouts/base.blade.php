<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Documento')</title>

    <style>
        @page {
            margin: 140px 25px 110px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* HEADER / FOOTER */
        header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 120px;
        }

        footer {
            position: fixed;
            bottom: -95px;
            left: 0;
            right: 0;
            height: 95px;
        }

        /* UTILIDADES */
        .border { border: 1px solid #000; }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .gray { background-color: #d9d9d9; }

        .mt-5 { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .mt-15 { margin-top: 15px; }

        th {
            font-size: 9px;
            padding: 6px 6px;
            line-height: 1.4;
            vertical-align: middle;
        }

        td {
            font-size: 9px;
            padding: 6px 6px;
            line-height: 1.4;
            vertical-align: middle;
        }

        .item-parent {
            background-color: #cfe2f3;
            font-weight: bold;
        }

        .item-child {
            padding-left: 14px;
        }

        .pagenum:before {
            content: counter(page);
        }

        .pagecount:before {
            content: counter(pages);
        }

        /* Hook para estilos específicos */
        @yield('styles')
    </style>
</head>

<body>

    {{-- HEADER --}}
    @include('pdf.partials.header')

    {{-- FOOTER --}}
    @include('pdf.partials.footer')

    <main>
        @yield('content')
    </main>

</body>
</html>
