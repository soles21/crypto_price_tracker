<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crypto Price Aggregator</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
<style>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 transition-colors duration-300">
    <!-- Blurred background shapes -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 -left-20 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute -top-8 -right-20 w-72 h-72 bg-yellow-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative min-h-screen">
        <!-- Navigation Bar -->
        <nav class="fixed top-0 left-0 right-0 z-50 backdrop-blur-xl bg-white/70 border-b border-gray-200/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo & Title -->
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-500" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 100-16 8 8 0 000 16zm-1-7v-3h2v3h3v2h-3v3h-2v-3H8v-2h3z"/>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-pink-600">
                            Crypto Tracker
                        </h1>
                    </div>

                    <!-- Digital Clock -->
                    <div class="flex items-center">
                        <livewire:digital-clock />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-20 pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="w-full">
                <livewire:price-grid />
            </div>
        </main>
    </div>

    @livewireScripts
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    
    <!-- Modified WebSocket handler -->
    <script>
    window.addEventListener('load', () => {
        if (window.Echo) {
            window.Echo.connector.pusher.connection.bind('message', (rawData) => {
                if (rawData.event === 'prices.updated' && rawData.data && rawData.data.prices) {
                    window.Livewire?.dispatch('prices-updated', [rawData.data]);
                }
            });
        }
    });
    </script>
    
</body>
</html>