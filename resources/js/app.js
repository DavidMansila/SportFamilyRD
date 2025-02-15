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
import home from './components/home/HomeVIew.vue';

// Create Vue application instance
const app = createApp({});

// Register components
app.component('example-component', ExampleComponent);
app.component('signup-component', SignUpView);
app.component('home-component', home);

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */
app.mount('#app');