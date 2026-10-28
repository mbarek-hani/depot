import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export function initEcho() {
    const key = import.meta.env.VITE_REVERB_APP_KEY;

    if (!key) {
        return null;
    }

    const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http';
    const port = Number(import.meta.env.VITE_REVERB_PORT || 8080);

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        withCredentials: true,
    });

    return window.Echo;
}
