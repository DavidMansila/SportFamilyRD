import "./bootstrap";
import { createApp } from "vue";
import { createRouter, createWebHistory } from "vue-router";

// Importa todos tus componentes
import App from "./components/App.vue";
import SignUpView from "./components/Login/SignUpView.vue";
// import HomeView from "./components/Home/HomeView.vue";
import HomeView from "./components/Home/HomeVIew.vue";
import DirectorioView from "./components/Directorio/DirectorioView.vue";
import NoticiasView from "./components/Noticias/NoticiasView.vue";
import CalendarioView from "./components/Calendario/CalendarioView.vue";
import TiendaView from "./components/Tienda/TiendaView.vue";
import EntrenadoresView from "./components/Entrenadores/EntrenadoresView.vue";
import SolicitudView from "./components/Entrenadores/SolicitudView.vue";
import SolicitudesUsuarios from "./components/Entrenadores/SolicitudesUsuarios.vue";
import SolicitudesEntrenadores from "./components/Admin/SolicitudesEntrenadores.vue";
import ForoView from "./components/Foro/ForoView.vue";
import AjustesView from "./components/Ajustes/AjustesView.vue";
import PerfilView from "./components/Perfil/PerfilView.vue";
import store from "./cartStore";

// Paginate
import VueAwesomePaginate from "vue-awesome-paginate";
import "vue-awesome-paginate/dist/style.css";

// Configuración del router
const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", component: HomeView },
        { path: "/signup", component: SignUpView },
        { path: "/directorio", component: DirectorioView },
        { path: "/noticias", component: NoticiasView },
        { path: "/calendario", component: CalendarioView },
        { path: "/tienda", component: TiendaView },
        { path: "/entrenadores", component: EntrenadoresView },
        { path: "/solicitud", component: SolicitudView },
        { path: "/solicitudes-usuarios", component: SolicitudesUsuarios },
        {
            path: "/solicitudes-entrenadores",
            component: SolicitudesEntrenadores,
        },
        { path: "/foro", component: ForoView },
        { path: "/ajustes", component: AjustesView },
        { path: "/perfil", component: PerfilView },
    ],
});

// app.js
import gsap from 'gsap';


// Crea la aplicación Vue
const app = createApp(App);

// Usa el router y VueAwesomePaginate
app.use(router);
app.use(VueAwesomePaginate);
app.use(store);

app.component("app-component", App);

app.use({
  install(app) {
    app.config.globalProperties.$gsap = gsap;
  }
});

app.mount("#app");
