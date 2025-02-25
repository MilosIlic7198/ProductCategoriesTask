require("./bootstrap");

import { createApp } from 'vue';
import App from './components/App.vue';
import { createRouter, createWebHistory } from 'vue-router';
import Routes from './routes';

const Router = createRouter({
    history: createWebHistory(),
    linkActiveClass: 'fw-bolder',
    routes: Routes,
});

const app = createApp(App);
app.use(Router);
app.mount('#app');
