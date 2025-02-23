import './bootstrap';
import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb', // or 'pusher'
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    disableStats: true
});

window.Echo.channel('crypto-prices')
    .listen('prices.updated', (data) => { 
        console.log('Price update received:', data);
        if (data.prices) {
            console.log('Received prices:', data.prices);
            if (window.Livewire) {
                window.Livewire.dispatch('prices-updated', [data]);
            }
        }
    });

window.Echo.connector.pusher.connection.bind('message', (data) => {
    console.log('Raw message received:', data);
});