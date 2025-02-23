<div wire:poll.10000ms="pollPrices" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
    @forelse($prices as $price)
        <livewire:price-card 
            :wire:key="$price['symbol']"
            :symbol="$price['symbol']"
            :price="$price['average_price']"
            :high="$price['high_price']"
            :low="$price['low_price']"
            :change="$price['price_change']"
            :changePercent="$price['price_change_percent']"
            :volume="$price['volume']"
            :timestamp="$price['timestamp']"
            :exchangeData="$price['exchange_data'] ?? []"
        />
    @empty
        <div class="col-span-full flex justify-center items-center p-12">
            <div class="text-center">
                <svg class="mx-auto w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No prices available</h3>
                <p class="mt-2 text-sm text-gray-500">Check back later for updates</p>
            </div>
        </div>
    @endforelse
</div>