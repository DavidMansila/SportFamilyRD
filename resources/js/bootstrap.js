import axios from "axios";

axios.defaults.baseURL = 'http://localhost:8000/api';

// Interceptor para agregar token
axios.interceptors.request.use((config) => {
    const token = sessionStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Manejar errores 401 (No autorizado)
// axios.interceptors.response.use(
//     (response) => response,
//     (error) => {
//         if (error.response && error.response.status === 401) {
//             sessionStorage.removeItem("token");
//             sessionStorage.removeItem("user");
//             window.location.href = "";
//         }
//         return Promise.reject(error);
//     }
// );
