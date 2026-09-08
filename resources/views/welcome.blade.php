<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Manager - Valutaconversie & Zoeken</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vite & CDN Fallback) -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>

    @livewireStyles
</head>
<body class="bg-slate-900 text-slate-100 font-sans min-h-screen antialiased selection:bg-indigo-500 selection:text-white">
    <!-- Navbar -->
    <header class="border-b border-slate-800 bg-slate-950/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/30">
                    €
                </div>
                <span class="font-bold text-lg text-white tracking-tight">OrderConverter <span class="text-xs font-normal px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">OER Live</span></span>
            </div>
            <div class="text-xs text-slate-400 hidden sm:block">
                Laravel {{ app()->version() }} | SQLite DB
            </div>
        </div>
    </header>

    <!-- Main Content Component -->
    <main class="py-6">
        <livewire:order-dashboard />
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Order Converter App &bull; Powered by OpenExchangeRates & Livewire</p>
    </footer>

    @livewireScripts
</body>
</html>
