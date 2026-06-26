# Gestor de Tareas — Frontend SPA

SPA construida sobre Laravel 13 + Inertia.js + Vue 3. Consume exclusivamente la API del backend a través de Axios, y el token JWT se guarda en `localStorage` y se maneja con Pinia.


## Lo que necesitás tener instalado

- PHP 8.3 o superior
- Composer 2.x
- Node.js 20+ (requerido por Vite 8 y laravel-vite-plugin 3)
- NPM


## Instalación paso a paso

Clona el repositorio y entrá a la carpeta del frontend:


git clone https://github.com/Tomas-coder-dev/Prueba-full-stack-laravel.git
cd frontend

Instalá las dependencias de PHP:


composer install

Instalá las dependencias de Node:


npm install


Copiá el archivo de entorno y generá la clave de la app:


cp .env.example .env
php artisan key:generate


Configurá las variables de entorno (ver sección siguiente).



## Variables de entorno

Solo necesitás ajustar estas dos líneas en el `.env`:


APP_URL=http://localhost:3000

VITE_API_BASE_URL=http://localhost:8000/api

`VITE_API_BASE_URL` le dice a Axios dónde está corriendo el backend. Si cambiás el puerto del backend, actualizá este valor.

---

## Cómo correrlo

Necesitás dos terminales abiertas al mismo tiempo:

# Terminal 1 — servidor PHP
php artisan serve --port=3000

# Terminal 2 — Vite (assets en tiempo real)
npm run dev

La app queda en `http://localhost:3000`.

O si preferís, podés usar el script `dev` del `composer.json` que levanta todo junto con `concurrently`:

composer dev


Para producción, compilá los assets primero:


npm run build
php artisan serve --port=3000


## Decisiones técnicas

### 1. Pinia para manejar el token JWT

Usé Pinia (`useAuthStore`) para centralizar el estado de autenticación en lugar de pasarlo con `provide/inject` o guardarlo en variables locales. Cualquier componente puede acceder al token y a los datos del usuario de forma reactiva. Al montar la app se restaura el token desde `localStorage` y se inyecta automáticamente en los headers de Axios.

### 2. Axios como cliente HTTP exclusivo

Aunque Inertia tiene su propio sistema de navegación, todas las llamadas a la API del backend se hacen con Axios configurado globalmente. Esto desacopla el consumo de datos del routing de Inertia y permite manejar el token, los errores y los headers desde un solo lugar.

### 3. Protección de rutas en `onMounted`

Las vistas que requieren autenticación (dashboard, tareas) verifican la existencia del token en `localStorage` al montarse. Si no hay token, redirigen al login. Es una solución práctica para el alcance del proyecto; en una app más grande se implementaría con un router guard global de Vue Router.

### 4. Frontend y backend como proyectos completamente independientes

Los dos proyectos son repositorios separados: el backend solo expone la API REST y el frontend la consume por HTTP. Esto permite que cada uno se despliegue, versione y escale de forma independiente, y que el backend pueda ser consumido por otros clientes en el futuro sin tocar nada.