<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    @vite('resources/sass/app.scss')
    <style>
        html {
            background-color: #F6F5FA;
        }
    </style>
</head>
<body>
    <div id="app" class="">
        @yield('content')
    </div>
    @vite('resources/js/app.js')
</body>
</html>


