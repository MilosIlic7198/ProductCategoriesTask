require("./bootstrap");

import Vue from 'vue';
import App from './components/App';
import VueRouter from "vue-router";
import Routes from "./routes";

Vue.use(VueRouter);

const Router = new VueRouter({
    mode: "history",
    linkActiveClass: "fw-bolder",
    routes: Routes,
})

new Vue({
    router: Router,
    components: { App },
    template: '<App />',
}).$mount("#app");
