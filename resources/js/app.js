require('./bootstrap');

window.Vue = require('vue');
Vue.component('search', require('./components/search.vue').default);
const app = new Vue({
    el: '#app',
})
