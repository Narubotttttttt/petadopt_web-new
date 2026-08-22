<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CAWS Pet Adoption') }}</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('images/caws-logo.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Prevent Alpine.js Flash of Unstyled Content (FOUC) & Theme Init -->
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F4F9F9] dark:bg-[#0B0F19] text-slate-800 dark:text-slate-100 selection:bg-[#199CA4] selection:text-white transition-colors duration-150">
        <div class="min-h-screen flex flex-col">
            <x-sidebar />

            <div class="lg:pl-64 flex flex-col flex-1 min-w-0">
                @include('layouts.navigation')

                @isset($header)
                    <header class="bg-white dark:bg-[#111827] border-b border-slate-200 dark:border-slate-800">
                        <div class="w-full py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
