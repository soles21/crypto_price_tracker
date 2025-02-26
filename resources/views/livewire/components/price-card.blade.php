<div>
    <div 
        wire:key="price-card-{{ $pair }}"
        x-data="{ 
            prevPrice: {{ $priceData['price'] ?? 0 }},
            priceChanged: false,
            currentPrice: {{ $priceData['price'] ?? 0 }},
            priceData: @js($priceData),
            pollIntervalId: null,
            showDetails: false,
            
            highlight: function() {
                this.priceChanged = true;
                var self = this;
                setTimeout(function() { 
                    self.priceChanged = false; 
                }, 2000);
            },
            
            checkForUpdates: function() {
                try {
                    var storedData = JSON.parse(localStorage.getItem('crypto_prices') || '{}');
                    var pairData = storedData['{{ $pair }}'];
                    
                    if (pairData && pairData.price !== this.currentPrice) {
                        this.prevPrice = this.currentPrice;
                        this.currentPrice = pairData.price;
                        this.priceData = pairData;
                        this.highlight();
                    }
                } catch (error) {
                    // Silent error handling
                }
            },
            
            startPolling: function() {
                var self = this;
                if (this.pollIntervalId) {
                    clearInterval(this.pollIntervalId);
                }
                this.pollIntervalId = setInterval(function() {
                    self.checkForUpdates();
                }, 2000);
            },
            
            setupEventListeners: function() {
                var self = this;
                document.addEventListener('crypto-price-updated', function(e) {
                    if (e.detail && e.detail.pair === '{{ $pair }}') {
                        self.checkForUpdates();
                    }
                });
            },
            
            cleanup: function() {
                if (this.pollIntervalId) {
                    clearInterval(this.pollIntervalId);
                }
            },
            
            toggleDetails: function() {
                this.showDetails = !this.showDetails;
            },

            formatPrice: function(price) {
                if (price > 1000) {
                    return price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (price > 1) {
                    return price.toLocaleString(undefined, {minimumFractionDigits: 4, maximumFractionDigits: 4});
                } else {
                    return price.toLocaleString(undefined, {minimumFractionDigits: 8, maximumFractionDigits: 8});
                }
            },
            
            getCoinIcon: function() {
                const pairName = '{{ $pair }}';
                if (pairName.includes('BTC')) return 'bitcoin';
                if (pairName.includes('ETH')) return 'ethereum';
                if (pairName.includes('BNB')) return 'binance';
                if (pairName.includes('SOL')) return 'solana';
                if (pairName.includes('DOGE')) return 'dogecoin';
                if (pairName.includes('XRP')) return 'ripple';
                if (pairName.includes('ADA')) return 'cardano';
                if (pairName.includes('DOT')) return 'polkadot';
                return 'generic';
            },
            
            getGradients: function() {
                const icon = this.getCoinIcon();
                switch(icon) {
                    case 'bitcoin': return 'from-amber-300 via-yellow-400 to-amber-500';
                    case 'ethereum': return 'from-indigo-300 via-blue-400 to-indigo-500';
                    case 'binance': return 'from-yellow-300 via-yellow-400 to-amber-400';
                    case 'solana': return 'from-fuchsia-400 via-purple-400 to-indigo-500';
                    case 'dogecoin': return 'from-yellow-300 via-amber-400 to-yellow-500';
                    case 'ripple': return 'from-blue-300 via-blue-400 to-indigo-400';
                    case 'cardano': return 'from-blue-400 via-indigo-400 to-blue-500';
                    case 'polkadot': return 'from-pink-400 via-fuchsia-500 to-purple-600';
                    default: return 'from-emerald-300 via-teal-400 to-cyan-500';
                }
            }
        }"
        x-init="
            startPolling();
            setupEventListeners();
            $cleanup = cleanup;
        "
        id="price-card-{{ $pair }}"
        class="rounded-[28px] overflow-hidden relative transition-all duration-500 ease-out"
        :class="{ 'translate-y-[-8px]': $data.priceChanged }"
    >
        <!-- Background pattern -->
        <div class="absolute inset-0 bg-white opacity-40 z-0"></div>
        <div class="absolute inset-0 bg-pattern opacity-5 z-0"></div>
        
        <!-- Glass effect card container -->
        <div class="relative z-10 backdrop-blur-sm bg-white/90 p-0.5 h-full">
            <!-- Gradient border effect -->
            <div class="absolute inset-0 rounded-[28px] p-0.5">
                <div class="absolute inset-0 rounded-[28px] bg-gradient-to-br" :class="getGradients()"></div>
            </div>
            
            <!-- Card content -->
            <div class="relative bg-white rounded-[26px] h-full flex flex-col overflow-hidden">
                <!-- Card top section with curved top -->
                <div class="h-28 bg-gradient-to-br rounded-t-[26px] overflow-hidden relative" :class="getGradients()">
                    <!-- Bubble animations in background -->
                    <div class="bubbles-container absolute inset-0">
                        <div class="bubble bubble-1"></div>
                        <div class="bubble bubble-2"></div>
                        <div class="bubble bubble-3"></div>
                        <div class="bubble bubble-4"></div>
                    </div>
                    
                    <!-- Coin symbol -->
                    <div class="absolute bottom-0 left-0 w-full">
                        <div class="relative flex justify-between items-center px-5 py-3">
                            <!-- Coin name and percentage -->
                            <div>
                                <h3 class="text-2xl font-extrabold text-white">{{ $pair }}</h3>
                                <div 
                                    class="mt-1 text-sm font-semibold inline-flex items-center px-2.5 py-1 rounded-full"
                                    :class="priceData.is_increasing ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                >
                                    <span x-show="priceData.is_increasing" class="mr-1">↑</span>
                                    <span x-show="!priceData.is_increasing" class="mr-1">↓</span>
                                    <span x-text="priceData.price_change_percentage ? priceData.price_change_percentage.toFixed(2) + '%' : '0.00%'"></span>
                                </div>
                            </div>
                            
                            <!-- Last updated timestamp -->
                            <div>
                                <div class="backdrop-blur-md bg-white/30 rounded-full px-3 py-1 text-xs text-white font-medium flex items-center space-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="priceData.updated_at ? new Date(priceData.updated_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Unknown'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Price display -->
                <div class="px-5 py-4 flex-grow">
                    <!-- Current price with animation -->
                    <div class="relative">
                        <div 
                            class="text-4xl tracking-tight font-black transition-all duration-500 flex items-center justify-center"
                            :class="{ 
                                'text-green-600 wobble-animation': priceData.is_increasing && $data.priceChanged,
                                'text-red-600 wobble-animation': !priceData.is_increasing && $data.priceChanged,
                                'text-gray-800': !$data.priceChanged
                            }"
                        >
                            <!-- Price value with floating symbol -->
                            <span class="relative">
                                <span class="absolute -left-4 top-0 text-lg opacity-70">$</span>
                                <span x-text="formatPrice(currentPrice)">{{ $this->formatPrice($priceData['price'] ?? 0) }}</span>
                            </span>
                        </div>
                        
                        <!-- Price change indicator -->
                        <div 
                            x-show="$data.priceChanged"
                            x-transition:enter="transition ease-out duration-300" 
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute left-0 right-0 flex justify-center"
                        >
                            <div class="text-sm rounded-full px-2 py-0.5 mt-1 font-medium inline-flex items-center" 
                                :class="priceData.is_increasing ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                <span x-text="priceData.is_increasing ? '↑' : '↓'" class="mr-1"></span>
                                <span x-text="formatPrice(Math.abs(currentPrice - prevPrice))"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Graph placeholder - we'll draw a simple SVG activity line -->
                    <div class="mt-3 h-16">
                        <svg class="w-full h-full" viewBox="0 0 100 30">
                            <defs>
                                <linearGradient id="graphGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" :stop-color="priceData.is_increasing ? '#22c55e' : '#ef4444'" stop-opacity="0.3"></stop>
                                    <stop offset="100%" :stop-color="priceData.is_increasing ? '#22c55e' : '#ef4444'" stop-opacity="0"></stop>
                                </linearGradient>
                            </defs>
                            
                            <!-- Line path will be dynamically generated but we use a dummy static one here -->
                            <path :d="priceData.is_increasing 
                                ? 'M0,20 Q10,25 20,15 T40,10 T60,15 T80,5 T100,10' 
                                : 'M0,10 Q10,5 20,15 T40,20 T60,15 T80,25 T100,20'"
                                :stroke="priceData.is_increasing ? '#22c55e' : '#ef4444'" 
                                fill="none" 
                                stroke-width="1.5"
                                class="chart-line">
                            </path>
                            
                            <!-- Area under the graph -->
                            <path :d="(priceData.is_increasing 
                                ? 'M0,20 Q10,25 20,15 T40,10 T60,15 T80,5 T100,10 L100,30 L0,30 Z' 
                                : 'M0,10 Q10,5 20,15 T40,20 T60,15 T80,25 T100,20 L100,30 L0,30 Z')"
                                fill="url(#graphGradient)">
                            </path>
                        </svg>
                    </div>
                </div>
                
                <!-- Card actions -->
                <div class="flex justify-between items-center px-5 py-3 border-t border-gray-100">
                    <!-- Activity indicator -->
                    <div class="flex items-center">
                        <div class="relative">
                            <div class="activity-pulse" :class="priceData.is_increasing ? 'bg-green-500' : 'bg-red-500'"></div>
                            <div :class="priceData.is_increasing ? 'bg-green-500' : 'bg-red-500'" class="h-2 w-2 rounded-full"></div>
                        </div>
                        <span class="ml-2 text-xs text-gray-500">Live</span>
                    </div>
                    
                    <!-- Details toggle button -->
                    <button 
                        @click="toggleDetails()"
                        class="flex items-center space-x-1 text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors group"
                    >
                        <span x-text="showDetails ? 'Hide Details' : 'Show Details'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" :class="showDetails ? 'rotate-180' : ''" class="h-4 w-4 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                
                <!-- Details panel -->
                <div 
                    x-show="showDetails" 
                    x-collapse 
                    class="border-t border-gray-100 px-5 py-4 bg-gray-50"
                >
                    <!-- Exchange chips -->
                    <div class="mb-3">
                        <div class="text-xs text-gray-500 font-medium mb-2">Available on</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-if="priceData.exchanges && priceData.exchanges.length">
                                <template x-for="exchange in priceData.exchanges" :key="exchange">
                                    <div class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200" x-text="exchange"></div>
                                </template>
                            </template>
                            <template x-if="!priceData.exchanges || !priceData.exchanges.length">
                                <div class="text-xs text-gray-400">No exchange data available</div>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Stats grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-500 font-medium">Previous Price</div>
                            <div class="mt-1 font-semibold text-gray-800" x-text="'$' + formatPrice(prevPrice)"></div>
                        </div>
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-500 font-medium">Price Change</div>
                            <div class="mt-1 font-semibold" :class="priceData.is_increasing ? 'text-green-600' : 'text-red-600'">
                                <span x-text="'$' + formatPrice(Math.abs(currentPrice - prevPrice))"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating 3D emblem effect -->
        <div 
            class="absolute right-3 top-6 emblem z-20 transform rotate-12" 
            :class="getGradients()"
        >
            <div class="w-12 h-12 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-xl">
                <span class="text-2xl font-black" x-text="'{{ $pair }}'.substring(0, 1)"></span>
            </div>
        </div>
        
        <!-- Pulse effect on update -->
        <div 
            x-show="$data.priceChanged"
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-500 ease-out"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-150"
            class="absolute inset-0 z-0 rounded-[28px]"
        >
            <div class="absolute inset-0 rounded-[28px]" :class="priceData.is_increasing ? 'ripple-effect-green' : 'ripple-effect-red'"></div>
        </div>
    </div>

    <style>
    /* Dynamic graph animation */
    .chart-line {
      stroke-dasharray: 110;
      stroke-dashoffset: 110;
      animation: drawLine 1.5s ease-out forwards;
    }
    
    @keyframes drawLine {
      to {
        stroke-dashoffset: 0;
      }
    }
    
    /* Card wobble animation */
    @keyframes wobble {
      0%, 100% { transform: translateX(0); }
      15% { transform: translateX(-5px) rotate(-1deg); }
      30% { transform: translateX(4px) rotate(1deg); }
      45% { transform: translateX(-3px) rotate(-0.5deg); }
      60% { transform: translateX(2px) rotate(0.5deg); }
      75% { transform: translateX(-1px) rotate(-0.25deg); }
    }
    
    .wobble-animation {
      animation: wobble 0.8s ease-in-out;
    }
    
    /* Background pattern */
    .bg-pattern {
      background-image: radial-gradient(circle at 1px 1px, #000 1px, transparent 0);
      background-size: 20px 20px;
    }
    
    /* Activity pulse animation */
    .activity-pulse {
      position: absolute;
      height: 10px;
      width: 10px;
      border-radius: 50%;
      opacity: 0.7;
      top: -4px;
      left: -4px;
      animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
      0% {
        transform: scale(0.7);
        opacity: 0.7;
      }
      70% {
        transform: scale(2);
        opacity: 0;
      }
      100% {
        transform: scale(0.7);
        opacity: 0;
      }
    }
    
    /* Floating emblem animation */
    .emblem {
      animation: float 3s ease-in-out infinite;
      transform-style: preserve-3d;
      perspective: 1000px;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(12deg); }
      50% { transform: translateY(-10px) rotate(12deg); }
    }
    
    /* Ripple effect animation */
    .ripple-effect-green, .ripple-effect-red {
      opacity: 0.3;
      border: 2px solid;
      animation: ripple 1s linear infinite;
    }
    
    .ripple-effect-green {
      border-color: #22c55e;
    }
    
    .ripple-effect-red {
      border-color: #ef4444;
    }
    
    @keyframes ripple {
      to {
        transform: scale(1.2);
        opacity: 0;
      }
    }
    
    /* Bubble animation in header gradient */
    .bubbles-container {
      overflow: hidden;
    }
    
    .bubble {
      position: absolute;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      animation: bubble-float linear infinite;
    }
    
    .bubble-1 {
      width: 30px;
      height: 30px;
      bottom: -30px;
      left: 10%;
      animation-duration: 6s;
    }
    
    .bubble-2 {
      width: 20px;
      height: 20px;
      bottom: -20px;
      left: 30%;
      animation-duration: 5s;
      animation-delay: 1s;
    }
    
    .bubble-3 {
      width: 15px;
      height: 15px;
      bottom: -15px;
      left: 60%;
      animation-duration: 7s;
    }
    
    .bubble-4 {
      width: 25px;
      height: 25px;
      bottom: -25px;
      left: 80%;
      animation-duration: 8s;
      animation-delay: 2s;
    }
    
    @keyframes bubble-float {
      0% {
        transform: translateY(0);
        opacity: 0;
      }
      20% {
        opacity: 0.8;
      }
      100% {
        transform: translateY(-120px);
        opacity: 0;
      }
    }
    </style>
</div>