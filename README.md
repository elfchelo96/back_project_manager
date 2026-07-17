# Project Manager API

Backend de Gestion de Proyectos e Incidencias (estilo Redmine / Jira / OpenProject), construido con **Laravel 12**, **PHP 8.4+** y **PostgreSQL**, usando **Laravel Sanctum** para autenticacion por tokens y **Spatie Laravel Permission** para roles y permisos. La API expone unicamente JSON y sigue una arquitectura por capas (Controllers -> Services -> Repositories -> Models) con Policies para autorizacion a nivel de objeto.

## ¿De qué trata el proyecto?

**Project Manager API** es el backend de una plataforma para planificar, organizar y dar seguimiento al trabajo de equipos. Centraliza la gestión de proyectos, tareas e incidencias, de modo que administradores, project managers y colaboradores puedan trabajar con información actualizada y según los permisos de su rol.

La API permite:

- Crear proyectos, incorporar miembros y definir categorías de trabajo.
- Gestionar tareas y subtareas con responsables, prioridades, estados, fechas y dependencias.
- Registrar comentarios, archivos adjuntos y horas trabajadas por tarea.
- Mantener documentación de cada proyecto mediante páginas wiki.
- Consultar un panel general, notificaciones y reportes de avance, productividad y horas registradas.
- Administrar usuarios, roles y permisos para controlar el acceso a cada función.

Está pensada para integrarse con un frontend web o móvil que consuma sus endpoints JSON, proporcionando una base sólida para soluciones de gestión de proyectos similares a Jira, Redmine u OpenProject.

## Tabla de contenidos

1. [¿De qué trata el proyecto?](#de-qué-trata-el-proyecto)
2. [Stack tecnologico](#stack-tecnologico)
3. [Instalacion](#instalacion)
4. [Credenciales por defecto](#credenciales-por-defecto)
5. [Arquitectura y decisiones de diseno](#arquitectura-y-decisiones-de-diseno)
6. [Modelo de autorizacion](#modelo-de-autorizacion)
7. [Formato de respuesta](#formato-de-respuesta)
8. [Endpoints de la API](#endpoints-de-la-api)
9. [Testing](#testing)
10. [Limitaciones conocidas](#limitaciones-conocidas)

## Stack tecnologico

- PHP 8.4+
- Laravel 12
- PostgreSQL 14+
- Laravel Sanctum 4 (autenticacion por tokens)
- Spatie Laravel Permission 6 (roles y permisos)
- PHPUnit 11 (pruebas, sobre SQLite en memoria)

## Instalacion

Este repositorio contiene **unicamente la capa de aplicacion personalizada** (migraciones, modelos, controladores, servicios, rutas, seeders, etc). No incluye el `vendor/` ni el esqueleto base de Laravel, ya que este debe generarse localmente con Composer. Siga estos pasos en orden:

```bash
# 1. Generar un proyecto Laravel 12 limpio
composer create-project laravel/laravel project-manager
cd project-manager

# 2. Copiar el contenido de este repositorio SOBRE el proyecto recien creado
#    (sobrescribiendo bootstrap/app.php, bootstrap/providers.php, composer.json, etc.)
#    Por ejemplo, si descomprimio este zip en ../project-manager-backend:
cp -r ../project-manager-backend/. .

# 3. Instalar las dependencias (Sanctum y Spatie Permission ya estan en composer.json)
composer install

# 4. Configurar el entorno
cp .env.example .env
php artisan key:generate

# 5. Editar .env con sus credenciales de PostgreSQL
#    DB_CONNECTION=pgsql
#    DB_HOST=127.0.0.1
#    DB_PORT=5432
#    DB_DATABASE=project_manager
#    DB_USERNAME=postgres
#    DB_PASSWORD=secret

# 6. Crear la base de datos en Postgres (fuera de Laravel)
createdb project_manager

# 7. Instalar Sanctum (publica su migracion, que este repo ya incluye en
#    database/migrations, asi que este paso solo registra el paquete)
php artisan install:api

# 8. Ejecutar migraciones y poblar datos de ejemplo
php artisan migrate
php artisan db:seed

# 9. Crear el enlace simbolico de almacenamiento (para adjuntos de tareas)
php artisan storage:link

# 10. Levantar el servidor de desarrollo
php artisan serve
```

La API quedara disponible en `http://localhost:8000/api`.

> **Nota:** los pasos 1 y 7 generan/registran archivos del esqueleto estandar de Laravel (config/app.php, config/auth.php, config/database.php, etc.) que **no fueron modificados** por este proyecto, ya que sus valores por defecto ya son compatibles con PostgreSQL y Sanctum a traves de las variables de entorno. Los unicos archivos de configuracion incluidos en este repositorio son los que si requieren personalizacion: `config/cors.php`, `config/sanctum.php` y `config/permission.php`.

## Credenciales por defecto

El seeder `UserSeeder` crea un usuario administrador (configurable via `ADMIN_EMAIL` / `ADMIN_PASSWORD` en `.env`) y un usuario de ejemplo por cada rol:

| Rol | Email | Password |
|---|---|---|
| Super Administrador | `admin@empresa.com` | `Admin123*` |
| Administrador | `ana.admin@empresa.com` | `Password123*` |
| Project Manager | `pedro.pm@empresa.com` | `Password123*` |
| Desarrollador | `diego.dev@empresa.com` / `daniela.dev@empresa.com` | `Password123*` |
| QA | `quentin.qa@empresa.com` | `Password123*` |
| Cliente | `carla.cliente@empresa.com` | `Password123*` |
| Invitado | `ivan.guest@empresa.com` | `Password123*` |

El seeder tambien crea 3 proyectos de ejemplo con miembros, categorias, tareas, comentarios, registros de tiempo y una pagina wiki inicial por proyecto.

## Arquitectura y decisiones de diseno

- **Capas**: `Controller` (HTTP, validacion via Form Requests, respuestas via `ApiResponser`) -> `Service` (logica de negocio, transacciones, auditoria) -> `Repository` (acceso a datos) -> `Model` (Eloquent). Las entidades centrales del dominio (User, Project, Task, TimeEntry, WikiPage) usan la abstraccion completa Repository + Service. Entidades de soporte mas simples (comentarios, adjuntos, notificaciones, roles/permisos, categorias, estados y prioridades) usan un Service mas liviano o Eloquent directo en el controlador, evitando una capa de Repository innecesaria para CRUDs triviales.
- **Identificadores**: las tres entidades principales (`users`, `projects`, `tasks`) usan `id` interno (bigint autoincremental, para joins eficientes) **y** un `uuid` publico unico, que es la clave usada en las rutas (`getRouteKeyName()` -> `uuid`). Las tablas de soporte usan unicamente `id` entero, manteniendo compatibilidad total con las tablas de Spatie Permission (que usan `model_id` entero).
- **Subtareas y dependencias**: las subtareas usan un `parent_id` autoreferencial simple. Las dependencias entre tareas (bloqueos) usan una tabla pivote `task_dependencies` independiente, permitiendo tipos (`blocks`, `relates_to`) y validando que una tarea no pueda cerrarse mientras tenga una dependencia bloqueante abierta (`Task::canBeClosed()`).
- **Auditoria de cambios**: la tabla `task_history` registra un cambio por fila (campo, valor anterior, valor nuevo) cada vez que cambia `status_id`, `priority_id` o `assigned_to` en una tarea. La tabla `activity_logs` registra eventos generales del sistema (login, creacion/edicion/borrado de proyectos, tareas y usuarios) a traves de `ActivityLogService`.
- **Notificaciones**: se implemento un modelo de notificaciones propio (`notifications`: `user_id`, `title`, `message`, `type`, `read_at`) en lugar de las notificaciones nativas de Laravel, para tener control total del esquema y simplificar el consumo desde el frontend.
- **Autorizacion en dos niveles**: middleware de Spatie (`permission`, `role`, `role_or_permission`) protege las rutas a nivel de modulo/accion; las Policies de Eloquent (`ProjectPolicy`, `TaskPolicy`, etc.) anaden verificaciones a nivel de objeto (pertenencia al proyecto, propiedad del recurso). El rol **Super Administrador** omite todas las Policies via `Gate::before` (pero sigue sujeto al middleware de rutas, donde tambien tiene el permiso correspondiente).
- **Respuestas JSON**: todas las respuestas siguen el sobre `{ success, message, data }` (mas `meta` con informacion de paginacion cuando aplica, y `errors` en caso de validacion fallida), implementado en el trait `App\Traits\ApiResponser` y en el manejador de excepciones de `bootstrap/app.php`.
- **PostgreSQL especifico**: las busquedas de texto (`scopeSearch` en `User`, `Project`, `Task`) usan el operador `ilike` de PostgreSQL para busqueda insensible a mayusculas. Esto significa que estos scopes especificos **no son portables a SQLite** (ver Limitaciones conocidas).

## Modelo de autorizacion

**Roles**: Super Administrador, Administrador, Project Manager, Desarrollador, QA, Cliente, Invitado.

**Permisos**: `projects.view|create|edit|delete`, `tasks.view|create|edit|delete`, `users.view|create|edit|delete`, `roles.manage`, `permissions.manage`, `reports.view`, `wiki.manage`, `time.manage`.

La asignacion de permisos por rol esta definida en `database/seeders/RoleSeeder.php` y puede ajustarse libremente desde ahi o en tiempo de ejecucion via los endpoints `/api/roles`.

## Formato de respuesta

```json
{
  "success": true,
  "message": "Operacion realizada correctamente",
  "data": { },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 68
  }
}
```

En caso de error:

```json
{
  "success": false,
  "message": "Los datos enviados no son validos.",
  "data": null,
  "errors": { "email": ["El campo email es obligatorio."] }
}
```

## Endpoints de la API

Todas las rutas, excepto las de `auth/login`, `auth/register`, `auth/forgot-password` y `auth/reset-password`, requieren el header `Authorization: Bearer {token}` (Sanctum) y una cuenta activa.

**Autenticacion**
`POST /api/auth/register` · `POST /api/auth/login` · `GET /api/auth/me` · `POST /api/auth/logout` · `POST /api/auth/logout-all` · `POST /api/auth/refresh` · `POST /api/auth/change-password` · `POST /api/auth/forgot-password` · `POST /api/auth/reset-password`

**Usuarios**
`GET /api/users` · `POST /api/users` · `GET /api/users/{user}` · `PUT /api/users/{user}` · `DELETE /api/users/{user}`

**Roles y permisos**
`GET|POST /api/roles` · `GET|PUT|DELETE /api/roles/{role}` · `PUT /api/roles/{role}/permissions` · `GET /api/permissions` · `GET /api/permissions/grouped`

**Proyectos**
`GET|POST /api/projects` · `GET|PUT|DELETE /api/projects/{project}`
`GET|POST|PUT /api/projects/{project}/members` · `DELETE /api/projects/{project}/members/{userId}`
`GET|POST /api/projects/{project}/categories` · `PUT|DELETE /api/projects/{project}/categories/{category}`
`GET|POST /api/projects/{project}/wiki` · `GET|PUT|DELETE /api/projects/{project}/wiki/{wikiPage}`

**Catalogos**
`GET /api/task-statuses` (+ `POST|PUT|DELETE` solo Administrador/Super Administrador)
`GET /api/task-priorities` (+ `POST|PUT|DELETE` solo Administrador/Super Administrador)

**Tareas**
`GET|POST /api/tasks` · `GET|PUT|DELETE /api/tasks/{task}` · `GET /api/tasks/{task}/history`
`POST /api/tasks/{task}/dependencies` · `DELETE /api/tasks/{task}/dependencies/{dependsOnTaskId}`
`GET|POST /api/tasks/{task}/comments` · `PUT|DELETE /api/tasks/{task}/comments/{comment}`
`GET|POST /api/tasks/{task}/attachments` · `GET /api/tasks/{task}/attachments/{attachment}/download` · `DELETE /api/tasks/{task}/attachments/{attachment}`

**Registro de horas**
`GET /api/time-entries` · `GET|POST /api/tasks/{task}/time-entries` · `PUT|DELETE /api/time-entries/{timeEntry}`

**Notificaciones**
`GET /api/notifications` · `GET /api/notifications/unread-count` · `POST /api/notifications/mark-all-read` · `PUT /api/notifications/{notification}/read` · `DELETE /api/notifications/{notification}`

**Reportes** (requieren `reports.view`)
`GET /api/reports/tasks-by-status` · `GET /api/reports/tasks-by-user` · `GET /api/reports/hours-worked` · `GET /api/reports/productivity` · `GET /api/reports/active-projects` · `GET /api/reports/finished-projects`

**Dashboard**
`GET /api/dashboard` -> `{ projects, tasks, completed_tasks, pending_tasks, users, hours_logged }`

## Testing

```bash
php artisan test
```

Las pruebas (`tests/Feature/AuthTest.php`, `ProjectTest.php`, `TaskTest.php`, `RoleTest.php`) corren sobre SQLite en memoria (ver `phpunit.xml`) usando `RefreshDatabase` y `Laravel\Sanctum\Sanctum::actingAs()`. Cubren registro/login, creacion y visibilidad de proyectos segun membresia, cambio de estado de tareas con auditoria en `task_history`, la regla de no-cierre con dependencias bloqueantes abiertas, y la gestion de roles.

## Limitaciones conocidas

- Los scopes `scopeSearch` de `User`, `Project` y `Task` usan el operador `ilike`, exclusivo de PostgreSQL. Funcionan correctamente en el entorno de produccion (Postgres) pero **no son compatibles con SQLite**; por eso las pruebas automatizadas no ejercitan el parametro `search` de los listados. Si necesita correr pruebas de busqueda, hagalo contra una base Postgres de pruebas o adapte el scope para usar `LOWER(...) LIKE LOWER(...)`.
- El codigo de este repositorio fue validado con `php -l` (analisis de sintaxis) sobre los 150+ archivos PHP, pero no pudo ejecutarse `composer install` ni `php artisan test` dentro del entorno donde se genero este proyecto, ya que dicho entorno no tiene acceso de red a `packagist.org`. Se recomienda ejecutar `composer install` y `php artisan test` apenas se complete la instalacion local para confirmar que todo funciona end-to-end.
#   b a c k _ p r o j e c t _ m a n a g e r  
 
