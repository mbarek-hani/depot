import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { initTheme } from './theme';
import { initEcho } from './echo';

initTheme();
initEcho();

createInertiaApp({
    title: (title) => (title ? `${title} · إدارة المخزون` : 'إدارة المخزون'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue');

        return pages[`./Pages/${name}.vue`]();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#facc15',
    },
});
