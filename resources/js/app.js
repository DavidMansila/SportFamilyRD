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
    // 'meta.title' es el nombre que sale en la pestaña del navegador.
    //
    // Antes cada vista se lo ponia sola en su mounted(), y las que no lo hacian
    // (Entrenadores, Foro, Directorio, Solicitud, Signup y las dos pantallas de
    // verificacion de correo) heredaban el titulo de la seccion anterior: se
    // entraba a Tienda, se pasaba a Entrenadores, y la pestaña seguia diciendo
    // "Tienda". Definido aqui es imposible que a una ruta se le olvide, porque
    // el router es lo unico que siempre se entera del cambio de seccion.
    //
    // Puede ser texto o una funcion de la ruta, para los casos que dependen de
    // un parametro o de la query.
    routes: [
        { path: "/", component: HomeView, meta: { title: "SportFamilyRD - Comunidad Deportiva Dominicana" } },
        {
            path: "/signup",
            component: SignUpView,
            // La misma ruta sirve el panel de entrar y el de registrarse.
            meta: { title: (route) => (route.query.panel === "signup" ? "Crear cuenta" : "Iniciar sesión") },
        },
        { path: "/directorio", component: DirectorioView, meta: { title: "Directorio de deportes" } },
        { path: "/noticias", component: NoticiasView, meta: { title: "Noticias" } },
        { path: "/calendario", component: CalendarioView, meta: { title: "Calendario" } },
        { path: "/tienda", component: TiendaView, meta: { title: "Tienda" } },
        { path: "/entrenadores", component: EntrenadoresView, meta: { title: "Entrenadores" } },
        { path: "/solicitud", component: SolicitudView, meta: { title: "Solicitud de entrenador" } },
        { path: "/solicitudes-usuarios", component: SolicitudesUsuarios, meta: { title: "Solicitudes de usuarios" } },
        {
            path: "/solicitudes-entrenadores",
            component: SolicitudesEntrenadores,
            meta: { title: "Solicitudes de entrenadores" },
        },
        { path: "/foro", component: ForoView, meta: { title: "Foro" } },
        { path: "/ajustes", component: AjustesView, meta: { title: "Ajustes" } },
        { path: "/perfil", component: PerfilView, meta: { title: "Perfil" } },
        { path: "/email/verified-success", component: EmailVerifiedSuccess, meta: { title: "Correo verificado" } },
        { path: "/email/verify/:id/:hash", component: VerificaApiCorreo, meta: { title: "Verificando correo" } },
    ],

    // Al cambiar de seccion se empieza desde arriba. Sin esto, vue-router deja
    // la pagina en la misma altura de scroll que traia: si el usuario estaba
    // abajo en el Home (por ejemplo en "Comunidad") y entraba a Noticias o al
    // Foro, caia a media pagina en vez del encabezado.
    //
    // 'savedPosition' solo tiene valor cuando se navega con las flechas de
    // atras/adelante del navegador; en ese caso se respeta donde estaba el
    // usuario, que es lo que uno espera al volver atras.
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }
        return { top: 0 };
    },
});

// Pone el titulo de la pestaña en CADA navegacion, incluidas las flechas de
// atras/adelante del navegador.
//
// Va en afterEach (y no en beforeEach) para que solo se aplique cuando la
// navegacion ya se confirmo: si se cancelara a mitad, la pestaña no se quedaria
// con el nombre de una seccion a la que nunca se llego.
//
// Corre antes del mounted() de la vista a proposito: asi una pantalla que
// necesite un titulo mas concreto -Perfil, que pone "Perfil de <nombre>" cuando
// carga el usuario- puede afinarlo despues sobre esta base, y mientras tanto la
// pestaña ya dice algo correcto en vez del nombre de la seccion anterior.
const TITULO_POR_DEFECTO = "SportFamilyRD";

router.afterEach((to) => {
    const titulo = to.meta?.title;
    document.title =
        typeof titulo === "function" ? titulo(to) : titulo || TITULO_POR_DEFECTO;
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
