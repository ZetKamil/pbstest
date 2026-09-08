<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-700/60">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Order Overzicht</h1>
            <p class="mt-1 text-sm text-slate-400">Bekijk en doorzoek bestellingen met automatische valutaconversie naar EUR.</p>
        </div>
        
        <!-- Live Rates Info Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-xs font-medium text-slate-300 shadow-sm">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span>OER Wisselkoersen (USD Basis): 
                <strong class="text-white">EUR: {{ number_format($rates['EUR'] ?? 0.90, 4) }}</strong> | 
                <strong class="text-white">GBP: {{ number_format($rates['GBP'] ?? 0.75, 4) }}</strong>
            </span>
        </div>
    </div>

    <!-- Search Input & Filter Control -->
    <div class="relative max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Zoek op gebruikersnaam..." 
            class="block w-full pl-10 pr-10 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition duration-150"
        />
        <!-- Loading Spinner -->
        <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
            <svg class="animate-spin h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Orders Table Container -->
    <div class="bg-slate-800/70 border border-slate-700/80 rounded-2xl shadow-xl overflow-hidden backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/60 border-b border-slate-700/80 text-xs font-semibold uppercase tracking-wider text-slate-300">
                        <th scope="col" class="px-6 py-4">Gebruikersnaam</th>
                        <th scope="col" class="px-6 py-4">Orderbedrag (origineel)</th>
                        <th scope="col" class="px-6 py-4">Orderbedrag (EUR)</th>
                        <th scope="col" class="px-6 py-4">Orderdatum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-sm text-slate-200">
                    @forelse ($orders as $order)
                        @php
                            $convertedEur = $currencyService->convertToEur((float) $order->amount, $order->user->currency_code);
                            $formattedOriginal = $currencyService->formatCurrency((float) $order->amount, $order->user->currency_code);
                            $formattedEur = $currencyService->formatCurrency($convertedEur, 'EUR');
                        @endphp
                        <tr class="hover:bg-slate-700/40 transition duration-150">
                            <!-- Gebruikersnaam -->
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-300 font-bold text-xs shadow-sm">
                                        {{ strtoupper(substr($order->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-100">{{ $order->user->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $order->user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Orderbedrag (origineel) -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-900/80 border border-slate-700 text-slate-200">
                                    {{ $formattedOriginal }}
                                </span>
                            </td>

                            <!-- Orderbedrag (EUR) -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                                    {{ $formattedEur }}
                                </span>
                            </td>

                            <!-- Orderdatum -->
                            <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                {{ \Carbon\Carbon::parse($order->ordered_at)->format('d-m-Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <p class="text-base font-medium">Geen bestellingen gevonden</p>
                                    <p class="text-xs text-slate-500">Probeer een andere zoekterm.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        @if ($orders->hasPages())
            <div class="px-6 py-4 bg-slate-900/50 border-t border-slate-700/80">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
