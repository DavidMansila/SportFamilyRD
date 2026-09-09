<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Titulo que sirve el servidor antes de que Vue arranque. En cuanto monta,
         el router lo sustituye por el de la seccion (ver el afterEach en app.js).
         Antes esto decia "Laravel" porque APP_NAME nunca se cambio. --}}
    <title>{{ config('app.name', 'SportFamilyRD') }}</title>

    <!-- Fonts y estilos -->
    {{-- Favicon: el icono de la pestaña. Antes existia public/favicon.ico pero
         estaba VACIO (0 bytes, el marcador que deja Laravel al instalar), y por
         eso el navegador pintaba su circulo gris por defecto.

         Es el bateador del logo recortado, sin el texto "SportFamilyRD": a 16
         pixeles el texto no se lee y solo ensucia. El .ico lleva 16, 32 y 48
         para que el navegador coja el tamaño exacto segun donde lo pinte
         (pestaña, favoritos, barra de tareas) en vez de reescalar uno solo. --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

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

    {{-- Font Awesome.
         La aplicacion usa 73 iconos con clases "fas"/"far" repartidos por
         todas las secciones, pero la hoja de estilos no se cargaba en ningun
         sitio: cada <i class="fas fa-..."> se pintaba como un elemento vacio.
         Se veia sobre todo en el pie del Home y en los pop-outs, donde los
         chips de contacto salian como circulos sin nada dentro.

         Se sirve desde cdnjs con integrity + crossorigin para que el navegador
         rechace el archivo si llegara alterado. El preconnect adelanta el
         handshake TLS, que si no se paga entero al descubrir el <link>. --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/js/bootstrap.js', 'resources/js/app.js', 'resources/css/app.css', 'resources/scss/app.scss'])
</head>

<body class="font-sans antialiased">
    <div id="app"></div>
</body>

</html>