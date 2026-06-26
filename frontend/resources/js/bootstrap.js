import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Apuntamos Axios a tu proyecto backend
window.axios.defaults.baseURL = 'http://127.0.0.1:8000/api';

// Interceptor para inyectar automáticamente el token JWT en cada petición
window.axios.interceptors.request.use(config => {
    const token = localStorage.getItem('jwt_token'); // O donde decidamos guardarlo
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});