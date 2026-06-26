# Prueba Técnica Fullstack — Gestor de Tareas

Solución completa para la prueba técnica de FireflyDigital. El sistema permite registrarse, iniciar sesión y gestionar tareas personales con filtros, prioridades y estados.

El repositorio está dividido en dos proyectos independientes:


/
├── back/    → API REST con Laravel 13 + JWT
└── front/   → SPA con Laravel 13 + Inertia.js + Vue 3


## Cómo levantar el proyecto completo

### 1. Cloná el repositorio


git clone https://github.com/Tomas-coder-dev/Prueba-full-stack-laravel.git
cd Prueba-full-stack-laravel


### 2. Levantá el backend primero


cd back
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve
```

La API queda en `http://localhost:8000`.

### 3. Levantá el frontend en otra terminal

cd front
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run dev &
php artisan serve --port=3000


La app queda en `http://localhost:3000`.


## Stack utilizado

| Proyecto | Tecnologías |
|----------|-------------|
| Backend  | PHP 8.3, Laravel 13, MySQL, tymon/jwt-auth 2.3 |
| Frontend | PHP 8.3, Laravel 13, Inertia.js, Vue 3, Pinia, TailwindCSS, Vite 8 |



## Instrucciones detalladas

Cada proyecto tiene su propio README con los pasos completos de instalación, variables de entorno y decisiones técnicas:

- [`back/README.md`](./back/README.md)
- [`front/README.md`](./front/README.md)



## Video de demostración

ttps://drive.google.com/drive/folders/1soAdSs9YV69yHZ9uS_T1B2xNTURHHsA9?usp=sharing 