<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Layout base Breeze/Vite:
             - Carga fuentes y assets con Vite.
             - Incluye @vite para CSS/JS de la app. -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo-uprm.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @auth
            {{-- Estructura para usuarios autenticados:
               - Header, Sidebar y contenedor principal con @yield('content') --}}
            @include('components.layout.header')
            <div class="d-flex">
                @include('components.layout.sidebar')
                <main class="flex-grow-1 p-3">
                    @yield('content')
                </main>
            </div>
        @endauth

        @guest
            {{-- Plantilla simple para pantallas de login/recuperación --}}
            @yield('content')
        @endguest
    </body>
</html>
