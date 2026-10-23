import Alpine from 'alpinejs';
import { initTheme } from './theme';
import { initCharts } from './charts';
import './notifications';

window.Alpine = Alpine;
Alpine.start();

initTheme();
initCharts();
