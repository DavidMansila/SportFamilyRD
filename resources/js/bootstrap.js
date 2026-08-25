import axios from "axios";

// axios.defaults.baseURL = 'http://18.191.50.161/api';
axios.defaults.baseURL = 'http://127.0.0.1:8080/api';
    // axios.defaults.baseURL = 'http://10.0.0.7:8000//api';

// axios.defaults.baseURL = 'http://10.0.0.6:8000/api';
// axios.defaults.baseURL = 'http://10.0.0.7:8000/api';
// axios.defaults.baseURL = 'http://10.193.2.172:8000/api';

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
