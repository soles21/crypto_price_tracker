import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

window.Echo.channel('crypto-prices')
    .listen('.crypto.price.updated', (e) => {
        console.log('Price updated:', e);
    });

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('WebSocket connection established');
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.error('WebSocket connection error:', error);
});