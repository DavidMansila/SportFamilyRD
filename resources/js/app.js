/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

import ExampleComponent from './components/ExampleComponent.vue';
import SignUpView from './components/Login/SignUpView.vue';
import HomeView from './components/home/HomeView.vue';
import NoticiasView from './components/noticias/NoticiasView.vue';
import CalendarioView from './components/calendario/CalendarioView.vue';
import TiendaView from './components/tienda/TiendaView.vue';
import EntrenadoresView from './components/entrenadores/EntrenadoresView.vue';
import ForoView from './components/foro/ForoView.vue';
import CrearPostView from './components/Foro/CrearPost.vue';




// Create Vue application instance
const app = createApp({});

// Register components
app.component('example-component', ExampleComponent);
app.component('signup-component', SignUpView);
app.component('home-component', HomeView);
app.component('noticias-component', NoticiasView);
app.component('calendario-component', CalendarioView);
app.component('tienda-component', TiendaView);
app.component('entrenadores-component', EntrenadoresView);
app.component('foro-component', ForoView);
app.component('crearpost-component', CrearPostView);





app.mount('#app');