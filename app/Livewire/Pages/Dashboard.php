<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\CryptoPrice;
use Illuminate\Support\Facades\Log;

class Dashboard extends Component
{
    public $prices = [];
    public $lastUpdate = null;
    public $loading = false;

    public function mount()
    {
        $this->loadPrices();
        Log::info('Dashboard mounted');
    }

    public function loadPrices()
    {
        $this->loading = true;
        $prices = [];
        
        $dbPrices = CryptoPrice::all();
        
        foreach ($dbPrices as $price) {
            $pair = $price->pair;
            $exchanges = $price->exchanges ?? [];
            
            $prices[$pair] = [
                'price' => (float) $price->price,
                'price_change_percentage' => (float) $price->price_change_percentage,
                'is_increasing' => $price->price_change_percentage > 0,
                'exchanges' => $exchanges,
                'updated_at' => $price->updated_at->toIso8601String(),
            ];
        }
        
        $this->prices = $prices;
        $this->lastUpdate = now()->toIso8601String();
        $this->loading = false;
        
        Log::info('Prices loaded from database', ['count' => count($prices)]);
    }

    public function refresh()
    {
        Log::info('Manual refresh requested');
        $this->loadPrices();
        $this->dispatch('dashboard-refreshed');
        
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.pages.dashboard')
            ->extends('layouts.app')
            ->section('content');
    }
}