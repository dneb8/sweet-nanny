import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { Icon } from '@iconify/vue';
import { vGsapVue } from 'v-gsap-nuxt/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { createPinia } from 'pinia';
import { vueDebounce } from 'vue-debounce';
// import { Toaster } from '@/components/ui/sonner';
// import 'vue-sonner/style.css';

const appName = import.meta.env.VITE_APP_NAME || 'SweetNanny';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue');
        const page = (await pages[`./Pages/${name}.vue`]()) as any;
        page.default.layout ??= AppLayout;

        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h('div', {}, [
                h(App, props), 
                // h(Toaster)     
            ])
        });

        app.use(plugin)
           .use(ZiggyVue)
           .use(createPinia())
           .component('Icon', Icon)
           .directive('gsap', vGsapVue())
           .directive('debounce', vueDebounce({ lock: true }))
           .mount(el);

        return app;
    },
    progress: {
        color: '#4B3563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
