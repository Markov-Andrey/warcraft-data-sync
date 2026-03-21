import './bootstrap';
import { createApp } from 'vue';
import ProjectApp from './components/ProjectApp.vue';
import UnitsApp from './components/UnitsApp.vue';

const projectEl = document.getElementById('project-app');
if (projectEl) {
    createApp(ProjectApp, JSON.parse(projectEl.dataset.props)).mount(projectEl);
}

const unitsEl = document.getElementById('units-app');
if (unitsEl) {
    createApp(UnitsApp, JSON.parse(unitsEl.dataset.props)).mount(unitsEl);
}
