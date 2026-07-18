import './bootstrap';
import './legacy';
import Alpine from 'alpinejs';
import { recipeApp } from './recipeApp';

window.Alpine = Alpine;
window.recipeApp = recipeApp;

Alpine.data('recipeApp', recipeApp);
Alpine.start();


