<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $preventAuthBackCache = request()->routeIs(
            'login',
            'register',
            'password.*',
            'verification.*',
            'two-factor.*'
        );
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>SiHP Research Data</title>   <!-- 👈 ตั้งชื่อแท็บเว็บตรงนี้ -->
        <link rel="icon" type="image/png" href="{{ asset('images/sihp-logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('images/sihp-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/sihp-logo.png') }}">

        @if ($preventAuthBackCache)
            <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
        @endif

        {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&family=Mitr:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="min-h-screen bg-[#f3f6fb] text-slate-900 antialiased" style="font-family: 'IBM Plex Sans Thai', sans-serif;">
        <div class="min-h-screen">
            {{ $slot }}
        </div>

        @livewireScripts

        @if ($preventAuthBackCache)
            <script>
                window.addEventListener('pageshow', function(event) {
                    const navEntry = window.performance?.getEntriesByType?.('navigation')?.[0];
                    const isBackForward = event.persisted || navEntry?.type === 'back_forward';

                    if (isBackForward) {
                        window.location.reload();
                    }
                });
            </script>
        @endif
    </body>
</html>
