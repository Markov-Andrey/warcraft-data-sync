import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import ProjectPage from './pages/ProjectPage.vue';
import UnitsPage from './pages/UnitsPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: ProjectPage },
        { path: '/units', component: UnitsPage },
    ],
});

createApp(App).use(router).mount('#app');
