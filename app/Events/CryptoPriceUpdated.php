<?php

namespace App\Events;

use App\Models\CryptoPrice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CryptoPriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The crypto price instance.
     *
     * @var CryptoPrice
     */
    public CryptoPrice $cryptoPrice;

    /**
     * Create a new event instance.
     */
    public function __construct(CryptoPrice $cryptoPrice)
    {
        $this->cryptoPrice = $cryptoPrice;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('crypto-prices'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->cryptoPrice->id,
            'pair' => $this->cryptoPrice->pair,
            'price' => (float) $this->cryptoPrice->price,
            'previous_price' => (float) $this->cryptoPrice->previous_price,
            'price_change' => (float) $this->cryptoPrice->price_change,
            'price_change_percentage' => (float) $this->cryptoPrice->price_change_percentage,
            'exchanges' => $this->cryptoPrice->exchanges,
            'is_increasing' => $this->cryptoPrice->isPriceIncreasing(),
            'updated_at' => $this->cryptoPrice->updated_at->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'crypto.price.updated';
    }
}