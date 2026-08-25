import "./bootstrap";
import { createApp } from "vue";
import { createRouter, createWebHistory } from "vue-router";

// App raíz: se necesita en todas las rutas, se mantiene estática.
import App from "./components/App.vue";
import store from "./cartStore";

// El resto de las vistas se cargan bajo demanda (import dinámico): cada ruta
// se convierte en su propio chunk de JS/CSS, así el navegador solo descarga
// el código de la página que el usuario realmente visita, en vez de las 16
// vistas completas en un único bundle en cada carga.
const HomeView = () => import('./components/home/HomeVIew.vue');
const SignUpView = () => import("./components/Login/SignUpView.vue");
const DirectorioView = () => import("./components/Directorio/DirectorioView.vue");
const NoticiasView = () => import("./components/Noticias/NoticiasView.vue");
const CalendarioView = () => import("./components/Calendario/CalendarioView.vue");
const TiendaView = () => import("./components/Tienda/TiendaView.vue");
const EntrenadoresView = () => import("./components/Entrenadores/EntrenadoresView.vue");
const SolicitudView = () => import("./components/Entrenadores/SolicitudView.vue");
const SolicitudesUsuarios = () => import("./components/Entrenadores/SolicitudesUsuarios.vue");
const SolicitudesEntrenadores = () => import("./components/Admin/SolicitudesEntrenadores.vue");
const ForoView = () => import("./components/Foro/ForoView.vue");
const AjustesView = () => import("./components/Ajustes/AjustesView.vue");
const PerfilView = () => import("./components/Perfil/PerfilView.vue");
const EmailVerifiedSuccess = () => import("./components/EmailVerifiedSuccess.vue");
const VerificaApiCorreo = () => import("./components/VerificaApiCorreo.vue");

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
        { path: "/email/verified-success", component: EmailVerifiedSuccess },
        { path: "/email/verify/:id/:hash", component: VerificaApiCorreo },
    ],
});

// Paginate
import VueAwesomePaginate from "vue-awesome-paginate";
import "vue-awesome-paginate/dist/style.css";

// Crea la aplicación Vue
const app = createApp(App);

// Usa el router y VueAwesomePaginate
app.use(router);
app.use(VueAwesomePaginate);
app.use(store);

app.component("app-component", App);

// gsap solo lo usa ForoView: se importa ahí mismo bajo demanda en vez de
// registrarlo globalmente, para no meterlo en el bundle inicial de toda la app.

app.mount("#app");
