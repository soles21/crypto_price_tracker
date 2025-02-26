<?php

namespace App\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;

class DigitalClock extends Component
{
    /**
     * Current time
     * 
     * @var string
     */
    public $currentTime;
    
    /**
     * Current date
     * 
     * @var string
     */
    public $currentDate;
    
    /**
     * Component mount
     */
    public function mount(): void
    {
        $this->currentTime = now()->format('H:i:s');
        $this->currentDate = now()->format('Y-m-d');
    }
    
    /**
     * Render the component
     */
    public function render(): View
    {
        return view('livewire.components.digital-clock');
    }
}