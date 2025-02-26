<?php

namespace App\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class PriceCard extends Component
{
    public $pair;
    public $priceData;
    public $recentlyUpdated = false;
    
    protected $listeners = [
        'dashboard-refreshed' => 'handleDashboardRefresh',
    ];
    
    public function mount($pair, $priceData)
    {
        $this->pair = $pair;
        $this->priceData = $priceData;
        Log::info("PriceCard mounted for {$pair}");
    }
    
    public function handleDashboardRefresh()
    {
        Log::info("Dashboard refresh received for {$this->pair}");
    }
    
    public function getLastUpdateTime(): string
    {
        if (!isset($this->priceData['updated_at'])) {
            return 'Unknown';
        }
        
        try {
            return \Carbon\Carbon::parse($this->priceData['updated_at'])->diffForHumans();
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    public function formatPrice(float $price): string
    {
        if ($price > 1000) {
            return number_format($price, 2);
        } elseif ($price > 1) {
            return number_format($price, 4);
        } else {
            return number_format($price, 8);
        }
    }
    
    public function formatPercentage(float $percentage): string
    {
        return number_format($percentage, 2) . '%';
    }
    
    public function getPriceChangeClasses(): string
    {
        if (!isset($this->priceData['is_increasing'])) {
            return '';
        }
        
        return $this->priceData['is_increasing'] ? 'text-green-600' : 'text-red-600';
    }
    
    public function render(): View
    {
        return view('livewire.components.price-card');
    }
}