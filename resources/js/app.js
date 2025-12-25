import { createApp } from 'vue'
import App from './App.vue'
import Home from './pages/Home.vue';
import Toast from './components/Toast.vue'
import '../css/api-docs.css'
import { createRouter, createWebHistory } from 'vue-router';
import { useToast } from './composables/useToast';

window.ApiInspector.basePath = '/' + window.ApiInspector.path;

if (! window.location.pathname.startsWith(window.ApiInspector.basePath)) {
  window.ApiInspector.basePath = window.location.pathname;
}

let routerBasePath = window.ApiInspector.basePath + '/';

if (window.ApiInspector.path === '' || window.ApiInspector.path === '/') {
  routerBasePath = '/';
  window.ApiInspector.basePath = '';
}

const router = createRouter({
  routes: [{
    path: window.ApiInspector.basePath,
    name: 'home',
    component: Home,
  }],
  history: createWebHistory(),
  base: routerBasePath,
});

const app = createApp(App)

window.useToast = useToast

app.component('Toast', Toast)
app.use(router);
app.mixin({
  computed: {
    ApiInspector: () => window.ApiInspector,
  },
});

app.mount('#api-inspector')
