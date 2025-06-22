import Echo from "laravel-echo";
import Pusher from "pusher-js";

// Configuración de Axios
import axios from 'axios';

axios.defaults.baseURL = 'http://localhost:8000/api';
axios.interceptors.request.use(config => {
    const token = sessionStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default axios;
// // Configuración de Echo y Pusher
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: "337abba0601b16bbbce2",
//     cluster: "mt1",
//     forceTLS: true,
//     encrypted: true,
//     authEndpoint: "/broadcasting/auth",
//     auth: {
//         headers: {
//             "X-CSRF-Token": getCookie("XSRF-TOKEN"),
//             Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
//             Accept: "application/json",
//         },
//     },
// });
