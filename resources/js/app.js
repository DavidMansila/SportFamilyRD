

import './bootstrap';
import { createApp } from 'vue';



import ExampleComponent from './components/ExampleComponent.vue';
import SignUpView from './components/Login/SignUpView.vue';
import HomeView from './components/home/HomeView.vue';
import NoticiasView from './components/Noticias/NoticiasView.vue';
import CalendarioView from './components/Calendario/CalendarioView.vue';
import TiendaView from './components/Tienda/TiendaView.vue';
import EntrenadoresView from './components/Entrenadores/EntrenadoresView.vue';
import SolicitudView from './components/Entrenadores/SolicitudView.vue';
import ForoView from './components/foro/ForoView.vue';
import ForoPublicaciones from './components/Foro/ForoPublicaciones.vue';
import CrearPostView from './components/Foro/CrearPost.vue';
import AjustesView from './components/Ajustes/AjustesView.vue';
//import CarritoView from './components/Carrito/CarritoView.vue';

//paginate
import VueAwesomePaginate from "vue-awesome-paginate";
import "vue-awesome-paginate/dist/style.css";



//import Store from './storage'; // Importa el store


// Create Vue application instance
const app = createApp({});



// Create Vue application instance
//const app = createApp(App)
//.use(Store) // Usa el store
//.mount('#app');

// Register components
app.component('example-component', ExampleComponent);
app.component('signup-component', SignUpView);


app.component('home-component', HomeView);


app.component('noticias-component', NoticiasView);


app.component('calendario-component', CalendarioView);


app.component('tienda-component', TiendaView);


app.component('entrenadores-component', EntrenadoresView);
app.component('solicitud-component', SolicitudView);


app.component('foro-component', ForoView);
app.component('crearpost-component', CrearPostView);
app.component('publicacion-component', ForoPublicaciones);

app.component('ajustes-component', AjustesView);


//app.component('carrito-component', CarritoView);

app.use(VueAwesomePaginate);
app.mount('#app');