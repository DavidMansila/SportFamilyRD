<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SportFamily') }}</title>

    <!-- Fonts y estilos -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    {{-- El hero del Home es el elemento LCP de la pagina, y su imagen se
         declara dentro del CSS de la vista (background-image), asi que el
         navegador no se entera de que existe hasta haber descargado y
         parseado ese CSS. Con el preload empieza a bajar a la vez que el
         HTML. Solo en "/": en el resto de las secciones no se usa. --}}
    @if (request()->path() === '/')
        <link rel="preload" as="image" href="/imagenes/estadio.jpg" fetchpriority="high">
    @endif

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/js/bootstrap.js', 'resources/js/app.js', 'resources/css/app.css', 'resources/scss/app.scss'])
</head>

<body class="font-sans antialiased">
    <div id="app"></div>
</body>

</html>