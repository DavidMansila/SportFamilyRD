import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        name: "Home",
        component: () => import("../components/Home/HomeView.vue"),
    },
    {
        path: "/signup",
        name: "SignUp",
        component: () => import("../components/Login/SignUpView.vue"),
    },
    {
        path: "/directorio",
        name: "Directorio",
        component: () => import("../components/Directorio/DirectorioView.vue"),
    },
    {
        path: "/noticias",
        name: "NoticiasComponent",
        component: () => import("../components/Noticias/NoticiasView.vue"),
    },
    {
        path: "/calendario",
        name: "CalendarioComponent",
        component: () => import("../components/Calendario/CalendarioView.vue"),
    },
    {
        path: "/tienda",
        name: "TiendaComponent",
        component: () => import("../components/Tienda/TiendaView.vue"),
    },
    {
        path: "/entrenadores",
        name: "EntrenadoresComponent",
        component: () =>
            import("../components/Entrenadores/EntrenadoresView.vue"),
    },
    {
        path: "/solicitud",
        name: "Solicitud",
        component: () => import("../components/Entrenadores/SolicitudView.vue"),
    },
    {
        path: "/solicitudes-usuarios",
        name: "SolicitudesUsuarios",
        component: () =>
            import("../components/Entrenadores/SolicitudesUsuarios.vue"),
    },
    {
        path: "/solicitudes-entrenadores",
        name: "SolicitudesEntrenadores",
        component: () =>
            import("../components/Admin/SolicitudesEntrenadores.vue"),
    },
    {
        path: "/foro",
        name: "ForoComponent",
        component: () => import("../components/Foro/ForoView.vue"),
    },
    {
        path: "/ajustes",
        name: "AjustesComponent",
        component: () => import("../components/Ajustes/AjustesView.vue"),
    },
    {
        path: "/perfil",
        name: "PerfilComponent",
        component: () => import("../components/Perfil/PerfilView.vue"),
    },

];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
