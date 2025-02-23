<div 
    x-data="{ 
        prevPrice: {{ $price }},
        priceChanged: false
    }"
    x-effect="
        if ($wire.price !== prevPrice) {
            priceChanged = true;
            setTimeout(() => priceChanged = false, 1000);
            prevPrice = $wire.price;
        }
    "
    class="relative overflow-hidden bg-white/80 backdrop-blur-sm rounded-xl border border-gray-200/50 shadow-lg transition duration-300 hover:shadow-xl"
>
    <!-- Card Content -->
    <div class="p-6">
        <!-- Header with Symbol and Time -->
        <div class="flex justify-between items-start mb-4">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                    <span class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-br from-purple-600 to-pink-600">
                        {{ substr($symbol, 0, 3) }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $symbol }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($timestamp)->diffForHumans() }}
                    </p>
                </div>
            </div>

            <!-- Price Change Badge -->
            <div 
                class="flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="{ 
                    'bg-green-100 text-green-800': {{ $change }} >= 0,
                    'bg-red-100 text-red-800': {{ $change }} < 0 
                }"
            >
                <svg 
                    class="w-4 h-4 mr-1"
                    :class="{ 'animate-bounce': priceChanged }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    @if($change >= 0)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                    @endif
                </svg>
                {{ number_format(abs($changePercent), 2) }}%
            </div>
        </div>

        <!-- Main Price Display -->
        <div 
            class="text-3xl font-bold text-gray-900 mb-4"
            :class="{ 'animate-pulse': priceChanged }"
        >
            ${{ number_format($price, 2) }}
            <span class="text-sm font-normal text-gray-500">USD</span>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="text-sm text-gray-500">24h High</div>
                <div class="mt-1 text-lg font-semibold text-green-600">
                    ${{ number_format($high, 2) }}
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="text-sm text-gray-500">24h Low</div>
                <div class="mt-1 text-lg font-semibold text-red-600">
                    ${{ number_format($low, 2) }}
                </div>
            </div>
        </div>

        <!-- Volume Bar -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500">Volume 24h</span>
                <span class="text-sm font-medium text-gray-900">
                    ${{ number_format($volume, 0) }}
                </span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div 
                    class="h-full bg-gradient-to-r from-purple-500 to-pink-500"
                    style="width: {{ min(($volume / 1000000000) * 100, 100) }}%"
                ></div>
            </div>
        </div>

        <!-- Exchange Data -->
        @if(!empty($exchangeData))
            <div class="border-t border-gray-200 pt-4 mt-4">
                <div class="text-sm font-medium text-gray-900 mb-3">Exchange Data</div>
                <div class="space-y-3">
                    @foreach($exchangeData as $exchange)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-900">
                                    {{ ucfirst($exchange['name'] ?? 'Unknown') }}
                                </span>
                                <span class="{{ ($exchange['change'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format($exchange['price'] ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-sm text-gray-500">
                                <div>Volume: ${{ number_format($exchange['volume'] ?? 0, 0) }}</div>
                                <div class="text-right">Change: {{ number_format($exchange['change_percent'] ?? 0, 2) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Price Change Flash Effect -->
    <div
        x-show="priceChanged"
        x-transition:enter="transition-opacity duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-gradient-to-r"
        :class="{
            'from-green-400/10 to-green-500/10': {{ $change }} >= 0,
            'from-red-400/10 to-red-500/10': {{ $change }} < 0
        }"
    ></div>
</div>