<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    @vite('resources/sass/app.scss')
</head>
<body>
    <div id="app" class="container">
        @yield('content')
    </div>
    @vite('resources/js/app.js')
</body>
</html>