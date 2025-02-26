<div>
<div 
    class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 relative overflow-hidden"
    x-data="{
        init() {
            // Add entrance animation to each card
            this.$nextTick(() => {
                let cards = document.querySelectorAll('[id^=price-card-]');
                cards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
            });
        }
    }"
    x-init="init()"
>
    <!-- Background decoration elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Large circles -->
        <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-gradient-to-br from-blue-500/10 to-purple-500/10 blur-2xl"></div>
        <div class="absolute -left-24 top-1/3 w-80 h-80 rounded-full bg-gradient-to-br from-amber-500/10 to-pink-500/10 blur-xl"></div>
        <div class="absolute right-1/4 bottom-0 w-64 h-64 rounded-full bg-gradient-to-br from-emerald-500/10 to-cyan-500/10 blur-xl"></div>
        
        <!-- Grid pattern -->
        <div class="absolute inset-0 bg-pattern opacity-[0.03]"></div>
    </div>

    <div class="relative py-10 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Dashboard Header -->
            <div class="relative rounded-[28px] overflow-hidden mb-12">
                <!-- Header content -->
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center p-8">
                    <div class="mb-6 md:mb-0">
                        <h1 class="text-4xl font-black tracking-tight">
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-violet-600">Crypto Dashboard</span>
                        </h1>
                        <p class="mt-2 text-gray-600 text-lg max-w-lg">
                            Real-time cryptocurrency market insights with live price updates
                        </p>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center">
                        <!-- Last updated info -->
                        <div class="flex items-center space-x-3 backdrop-blur-sm bg-white/70 rounded-2xl p-3 pr-4 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-center bg-gradient-to-br from-blue-500 to-violet-500 h-10 w-10 rounded-xl text-white shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">Last updated</div>
                                <div class="font-medium text-gray-800">
                                    @if($lastUpdate)
                                        {{ \Carbon\Carbon::parse($lastUpdate)->format('H:i:s') }}
                                    @else
                                        --:--:--
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Refresh button -->
                        <button 
                            wire:click="refresh" 
                            wire:loading.attr="disabled"
                            class="relative overflow-hidden group px-5 py-3 bg-gradient-to-r from-blue-600 to-violet-600 rounded-xl text-white font-medium shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.03] active:scale-[0.98]"
                        >
                            <div class="absolute inset-0 bg-white/20 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                            <div class="relative flex items-center space-x-2">
                                <svg wire:loading.remove wire:target="refresh" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <svg wire:loading wire:target="refresh" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="refresh">Refresh Prices</span>
                                <span wire:loading wire:target="refresh">Refreshing...</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Price Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($prices as $pair => $priceData)
                    <div wire:key="price-container-{{ $pair }}" class="transform transition-all duration-500">
                        @livewire('components.price-card', ['pair' => $pair, 'priceData' => $priceData], key("price-card-{$pair}"))
                    </div>
                @empty
                    <div class="col-span-full py-16">
                        <div class="flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm rounded-[28px] p-10 shadow-sm border border-gray-100 text-center">
                            <!-- Empty state illustration -->
                            <div class="relative mb-6">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-violet-500/10 rounded-full blur-xl"></div>
                                <div class="relative bg-white/90 backdrop-blur-sm rounded-full p-5 shadow-sm border border-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-gray-800 text-xl font-bold mb-3">No Price Data Available</div>
                            <p class="text-gray-500 mb-6 max-w-md">Looks like there are no cryptocurrency prices to display right now. Click the button below to fetch the latest data.</p>
                            <button 
                                wire:click="refresh" 
                                class="relative overflow-hidden group px-6 py-3 bg-gradient-to-r from-blue-600 to-violet-600 rounded-xl text-white font-medium shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.03] active:scale-[0.98]"
                            >
                                <div class="absolute inset-0 bg-white/20 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                                <div class="relative flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Load Prices</span>
                                </div>
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
/* Grid pattern */
.bg-pattern {
  background-image: radial-gradient(circle at 1px 1px, #000 1px, transparent 0);
  background-size: 40px 40px;
}
</style>
</div>