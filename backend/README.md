# Gestor de Tareas — Backend API

API REST construida con Laravel 13 y autenticación JWT. Cada usuario gestiona únicamente sus propias tareas, con filtros por estado, prioridad y búsqueda por título.


## Lo que necesitás tener instalado

- PHP 8.3 o superior
- Composer 2.x
- MySQL 8.0
- Node.js 20+ y NPM



## Instalación paso a paso

Clona el repositorio y entrá a la carpeta del backend:


git clone (https://github.com/Tomas-coder-dev/Prueba-full-stack-laravel.git)
cd backend


Instalá las dependencias de PHP:


composer install


Copiá el archivo de entorno y generá la clave de la app:


cp .env.example .env
php artisan key:generate


Generá el secret para JWT (esto actualiza `JWT_SECRET` en tu `.env`):


php artisan jwt:secret


Creá la base de datos en MySQL y luego corré las migraciones:


php artisan migrate




## Variables de entorno

El `.env` que necesitás configurar antes de correr el proyecto:


APP_NAME=Laravel
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prueba_tecnica_db
DB_USERNAME=root
DB_PASSWORD=admin

JWT_SECRET=        # se genera con: php artisan jwt:secret


El resto de variables que vienen por defecto en el `.env.example` no necesitan cambios para correr el proyecto en local.



## Cómo correrlo


php artisan serve

La API queda disponible en `http://localhost:8000/api`.


## Endpoints disponibles

**Autenticación**

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/auth/register` | Crear cuenta |
| POST | `/api/auth/login` | Iniciar sesión → devuelve el JWT |
| POST | `/api/auth/logout` | Cerrar sesión |

**Tareas** *(requieren el header `Authorization: Bearer <token>`)*

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/dashboard` | Métricas generales |
| GET | `/api/tasks` | Listar tareas del usuario |
| POST | `/api/tasks` | Crear tarea |
| PUT | `/api/tasks/{id}` | Editar tarea |
| DELETE | `/api/tasks/{id}` | Eliminar tarea |
| PATCH | `/api/tasks/{id}/status` | Cambiar solo el estado |

Los filtros se pasan como query params:

GET /api/tasks?status=pending
GET /api/tasks?priority=high
GET /api/tasks?search=laravel


## Decisiones técnicas

### 1. JWT con `tymon/jwt-auth` en lugar de Sanctum

Sanctum funciona muy bien cuando el frontend y el backend comparten el mismo dominio, pero acá son proyectos completamente separados corriendo en puertos distintos. Con JWT el token viaja en el header `Authorization`, lo cual simplifica mucho la integración con Axios desde el frontend sin tener que lidiar con cookies ni CSRF.

### 2. Validación con Form Requests

En vez de validar los datos directamente en los controladores, toda la lógica de validación vive en clases `FormRequest` separadas. Esto mantiene los controladores enfocados en lo que les corresponde y hace que agregar o cambiar reglas de validación sea mucho más limpio.

### 3. Tarea en estado `done` no editable desde la API

Esta restricción se aplica en el controlador, no solo en el frontend. Si alguien intenta editar una tarea completada directamente contra la API, recibe un `403 Forbidden`. Así la regla de negocio se respeta independientemente de quién consuma la API.

### 4. Scope de tareas por usuario autenticado

Todas las queries de tareas se hacen a través de `$request->user()->tasks()`, nunca con `Task::all()`. De esta forma cada usuario solo puede ver y modificar sus propias tareas sin necesidad de validaciones extra en cada endpoint.