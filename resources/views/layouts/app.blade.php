<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Crypto Price Tracker') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire -->
    @livewireStyles
    
    <style>
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .app-logo {
      filter: drop-shadow(0 10px 8px rgb(0 0 0 / 0.04)) drop-shadow(0 4px 3px rgb(0 0 0 / 0.1));
    }
    
    .bg-pattern {
      background-image: radial-gradient(circle at 1px 1px, #000 1px, transparent 0);
      background-size: 40px 40px;
    }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 to-slate-100 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <header class="relative overflow-hidden">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -right-20 -top-20 w-64 h-64 rounded-full bg-gradient-to-br from-blue-500/10 to-purple-500/10 blur-2xl"></div>
                <div class="absolute -left-20 top-1/2 w-48 h-48 rounded-full bg-gradient-to-br from-amber-500/10 to-pink-500/10 blur-xl"></div>
                <div class="absolute inset-0 bg-pattern opacity-[0.02]"></div>
            </div>
            
            <div class="relative z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <!-- Logo/App Name -->
                            <div class="flex items-center relative">
                                <div class="relative z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 app-logo" viewBox="0 0 20 20" fill="url(#logo-gradient)">
                                        <defs>
                                            <linearGradient id="logo-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stop-color="#3B82F6" />
                                                <stop offset="100%" stop-color="#8B5CF6" />
                                            </linearGradient>
                                        </defs>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <h1 class="ml-2 text-2xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-violet-600">
                                    {{ config('app.name', 'Crypto Price Tracker') }}
                                </h1>
                            </div>
                        </div>
                        @livewire('components.digital-clock')
                    </div>
                </div>
            </div>
            
            <div class="h-[1px] bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
        </header>

        <main class="flex-grow">
            @yield('content')
        </main>
        
        <footer class="relative overflow-hidden">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute inset-0 bg-pattern opacity-[0.02]"></div>
            </div>
            
            <div class="relative z-10 border-t border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <div class="text-sm text-gray-500">
                            &copy; {{ date('Y') }} Crypto Price Tracker. All rights reserved.
                        </div>
                        
                        <div class="flex items-center space-x-6">
                            <a href="#" class="text-gray-500 hover:text-gray-800 transition-colors">
                                <span class="text-sm">Privacy Policy</span>
                            </a>
                            <a href="#" class="text-gray-500 hover:text-gray-800 transition-colors">
                                <span class="text-sm">Terms of Service</span>
                            </a>
                            <a href="#" class="text-gray-500 hover:text-gray-800 transition-colors">
                                <span class="text-sm">Contact</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    @livewireScripts
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateClock() {
            const now = new Date();
            const timeElement = document.getElementById('digital-clock-time');
            const timezoneElement = document.getElementById('digital-clock-timezone');
            
            if (timeElement) {
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const seconds = now.getSeconds().toString().padStart(2, '0');
                timeElement.textContent = `${hours}:${minutes}:${seconds}`;
            }
            
            if (timezoneElement) {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                const timezoneAbbr = now.toLocaleTimeString('en-US', { timeZoneName: 'short' }).split(' ')[2];
                timezoneElement.textContent = timezoneAbbr;
                timezoneElement.title = timezone;
            }
            
            setTimeout(updateClock, 1000);
        }
        
        updateClock();
    });

    /**
     * Crypto price update handling via localStorage
     * 
     * This approach decouples WebSocket events from Livewire's component lifecycle,
     * avoiding "Snapshot missing" errors by using localStorage as a mediator
     */
    
    function storePriceUpdate(data) {
        try {
            let storedPrices = JSON.parse(localStorage.getItem('crypto_prices') || '{}');
            storedPrices[data.pair] = {
                price: data.price,
                price_change_percentage: data.price_change_percentage,
                is_increasing: data.is_increasing,
                exchanges: data.exchanges || [],
                updated_at: new Date().toISOString()
            };
            
            localStorage.setItem('crypto_prices', JSON.stringify(storedPrices));
            document.dispatchEvent(new CustomEvent('crypto-price-updated', { 
                detail: { pair: data.pair, data: storedPrices[data.pair] }
            }));
            
            return true;
        } catch (error) {
            return false;
        }
    }

    if (!localStorage.getItem('crypto_prices')) {
        localStorage.setItem('crypto_prices', '{}');
    }

    document.addEventListener('livewire:initialized', () => {
        if (typeof Echo !== 'undefined') {
            Echo.channel('crypto-prices')
                .listen('.crypto.price.updated', (data) => {
                    storePriceUpdate(data);
                });
        }
    });
</script>
</body>
</html>