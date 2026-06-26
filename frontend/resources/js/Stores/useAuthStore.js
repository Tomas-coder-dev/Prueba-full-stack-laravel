import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('jwt_token') || null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(credentials) {
            // Hacemos la petición a tu API independiente (el backend)
            const response = await axios.post('/login', credentials);
            
            // Guardamos los datos en el estado de Pinia
            this.token = response.data.token;
            this.user = response.data.user;

            // Guardamos en LocalStorage para que no se pierda al recargar la página
            localStorage.setItem('jwt_token', this.token);
            localStorage.setItem('user', JSON.stringify(this.user));

            // Aseguramos que Axios use el nuevo token en futuras peticiones
            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        },

        async logout() {
            try {
                // Intentamos cerrar sesión en la API
                if (this.token) {
                    await axios.post('/logout');
                }
            } catch (error) {
                console.error('Error al cerrar sesión en el servidor', error);
            } finally {
                // Limpiamos el estado y LocalStorage sin importar si la API falló
                this.user = null;
                this.token = null;
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user');
                delete axios.defaults.headers.common['Authorization'];
            }
        }
    }
});