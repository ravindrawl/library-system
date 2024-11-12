import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import { createApp } from 'vue/dist/vue.esm-bundler.js';
import BookComponent from './components/BookList.vue';

const app = createApp({});
app.component('book-list', BookComponent);
if (process.env.NODE_ENV === 'development') {
    app.config.devtools = true;
}
// Mount Vue to a specific element in your Blade template
app.mount('#app');