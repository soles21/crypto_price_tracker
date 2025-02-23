<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\CryptoPrice;
use Livewire\Attributes\On;

class PriceCard extends Component
{
    public $symbol;
    public $price;
    public $high;
    public $low;
    public $change;
    public $changePercent;
    public $volume;
    public $timestamp;
    public $exchangeData = [];
    
    public function mount($symbol)
    {
        $this->symbol = $symbol;
        $this->fetchLatestPriceData();
    }

    // Polling method
    public function pollPrice()
    {
        $this->fetchLatestPriceData();
    }

    // WebSocket event handler
    #[On('prices-updated')]
    public function handleWebSocketUpdate($data)
    {
        if (!isset($data['prices'][$this->symbol])) {
            return;
        }

        $symbolData = $data['prices'][$this->symbol];
        $this->updatePriceDetails($symbolData);
    }

    protected function fetchLatestPriceData()
    {
        try {
            $latestPrice = CryptoPrice::whereHas('pair', function ($query) {
                    $query->where('symbol', $this->symbol);
                })
                ->with('exchange')
                ->latest('fetched_at')
                ->first();

            if ($latestPrice) {
                $this->price = $latestPrice->price;
                $this->high = $latestPrice->high;
                $this->low = $latestPrice->low;
                $this->change = $latestPrice->price_change;
                $this->changePercent = $latestPrice->price_change_percent;
                $this->volume = $latestPrice->volume;
                $this->timestamp = $latestPrice->fetched_at->toIso8601String();

                if ($latestPrice->exchange) {
                    $this->exchangeData = [[
                        'name' => $latestPrice->exchange->name,
                        'price' => $latestPrice->price,
                        'volume' => $latestPrice->volume,
                        'change' => $latestPrice->price_change,
                        'change_percent' => $latestPrice->price_change_percent
                    ]];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch latest price data', [
                'symbol' => $this->symbol,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function updatePriceDetails($symbolData)
    {
        try {
            $exchangeName = key($symbolData);
            $priceData = $symbolData[$exchangeName];

            $this->price = $priceData['price'] ?? $this->price;
            $this->high = $priceData['high'] ?? $this->high;
            $this->low = $priceData['low'] ?? $this->low;
            $this->change = $priceData['priceChange'] ?? $this->change;
            $this->changePercent = $priceData['priceChangePercent'] ?? $this->changePercent;
            $this->volume = $priceData['volume'] ?? $this->volume;
            $this->timestamp = now()->toIso8601String();

            $this->updateExchangeData($symbolData);
        } catch (\Exception $e) {
            Log::error('Price update failed', [
                'symbol' => $this->symbol,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function updateExchangeData($symbolData)
    {
        $this->exchangeData = [];
        
        foreach ($symbolData as $exchangeName => $priceData) {
            $this->exchangeData[] = [
                'name' => $exchangeName,
                'price' => $priceData['price'] ?? 0,
                'volume' => $priceData['volume'] ?? 0,
                'change' => $priceData['priceChange'] ?? 0,
                'change_percent' => $priceData['priceChangePercent'] ?? 0
            ];
        }
    }

    public function render()
    {
        return view('livewire.price-card');
    }
}