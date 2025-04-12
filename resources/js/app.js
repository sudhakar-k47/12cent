import './bootstrap';
import '../scss/config/default/app.scss';
import '@vueform/slider/themes/default.css';
import '../scss/mermaid.min.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js'; // ✅ Correct import for Ziggy
import BootstrapVueNext from 'bootstrap-vue-next';
import vClickOutside from "click-outside-vue3";
import VueApexCharts from "vue3-apexcharts";
import VueFeather from 'vue-feather';
import VueTheMask from 'vue-the-mask';

import AOS from 'aos';
import 'aos/dist/aos.css';

import store from "./state/store";
import i18n from './i18n';

AOS.init({
    easing: 'ease-out-back',
    duration: 1000
});

createInertiaApp({
    title: title => title ? `${title} | 21st Century.in` : '21st Century.in',
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(store)
            .use(i18n)
            .use(ZiggyVue) // 💡 works correctly now
            .use(BootstrapVueNext)
            .use(VueApexCharts)
            .use(VueTheMask)
            .use(vClickOutside)
            .component(VueFeather.type, VueFeather)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
