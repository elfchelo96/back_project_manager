# APIs de Roles y Permisos

## Autenticación
Todos los endpoints requieren autenticación via Bearer Token (Sanctum) y que el usuario tenga `is_active = true`.

```
Authorization: Bearer {token}
```

---

## Roles

### Listar Roles
**GET** `/api/roles`

Requiere permiso: `roles.manage`

**Response (200):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "name": "Super Administrador",
      "is_system": true,
      "permissions": null,
      "permissions_count": 17,
      "users_count": 1,
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

---

### Crear Rol
**POST** `/api/roles`

Requiere permiso: `roles.manage`

**Body:**
```json
{
  "name": "Editor",
  "permissions": ["projects.view", "tasks.view", "tasks.create"]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Rol creado correctamente.",
  "data": {
    "id": 8,
    "name": "Editor",
    "is_system": false,
    "permissions": ["projects.view", "tasks.view", "tasks.create"],
    "permissions_count": 3,
    "users_count": 0,
    "created_at": "2024-06-27T10:30:00Z"
  }
}
```

---

### Ver Rol
**GET** `/api/roles/{role}`

Requiere permiso: `roles.manage`

**Response (200):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1,
    "name": "Super Administrador",
    "is_system": true,
    "permissions": [
      "projects.view", "projects.create", "projects.edit", "projects.delete",
      "tasks.view", "tasks.create", "tasks.edit", "tasks.delete",
      "users.view", "users.create", "users.edit", "users.delete",
      "roles.manage", "permissions.manage", "reports.view", "wiki.manage", "time.manage"
    ],
    "permissions_count": 17,
    "users_count": 1,
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

---

### Actualizar Rol
**PUT/PATCH** `/api/roles/{role}`

Requiere permiso: `roles.manage`

**Nota:** Los roles del sistema (`Super Administrador`, `Administrador`) no pueden ser renombrados.

**Body:**
```json
{
  "name": "Editor Mejorado"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Rol actualizado correctamente.",
  "data": { ... }
}
```

**Error (422) - Rol del sistema:**
```json
{
  "success": false,
  "message": "No se puede modificar el rol del sistema: Super Administrador",
  "errors": null
}
```

---

### Eliminar Rol
**DELETE** `/api/roles/{role}`

Requiere permiso: `roles.manage`

**Nota:** Los roles del sistema (`Super Administrador`, `Administrador`) no pueden ser eliminados.

**Response (200):**
```json
{
  "success": true,
  "message": "Rol eliminado correctamente.",
  "data": null
}
```

**Error (422) - Rol del sistema:**
```json
{
  "success": false,
  "message": "No se puede eliminar el rol del sistema: Super Administrador",
  "errors": null
}
```

---

### Sincronizar Permisos (Reemplazar Todos)
**PUT** `/api/roles/{role}/permissions`

Requiere permiso: `permissions.manage`

Reemplaza **todos** los permisos del rol con los indicados.

**Body:**
```json
{
  "permissions": ["projects.view", "tasks.view", "tasks.create"]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Permisos sincronizados correctamente.",
  "data": {
    "id": 8,
    "name": "Editor",
    "is_system": false,
    "permissions": ["projects.view", "tasks.view", "tasks.create"],
    "permissions_count": 3,
    "users_count": 0,
    "created_at": "2024-06-27T10:30:00Z"
  }
}
```

---

### Agregar Permisos (Sin Reemplazar)
**POST** `/api/roles/{role}/permissions`

Requiere permiso: `permissions.manage`

Agrega los permisos indicados sin eliminar los existentes.

**Body:**
```json
{
  "permissions": ["tasks.edit", "tasks.delete"]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Permisos agregados correctamente.",
  "data": {
    "id": 8,
    "name": "Editor",
    "is_system": false,
    "permissions": ["projects.view", "tasks.view", "tasks.create", "tasks.edit", "tasks.delete"],
    "permissions_count": 5,
    "users_count": 0,
    "created_at": "2024-06-27T10:30:00Z"
  }
}
```

---

### Quitar Permisos (Sin Eliminar Todos)
**DELETE** `/api/roles/{role}/permissions`

Requiere permiso: `permissions.manage`

Elimina solo los permisos indicados.

**Body:**
```json
{
  "permissions": ["tasks.delete"]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Permisos removidos correctamente.",
  "data": {
    "id": 8,
    "name": "Editor",
    "is_system": false,
    "permissions": ["projects.view", "tasks.view", "tasks.create", "tasks.edit"],
    "permissions_count": 4,
    "users_count": 0,
    "created_at": "2024-06-27T10:30:00Z"
  }
}
```

---

### Listar Usuarios con un Rol
**GET** `/api/roles/{role}/users`

Requiere permiso: `roles.manage`

Devuelve todos los usuarios que tienen asignado este rol.

**Response (200):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "firstname": "Juan",
      "lastname": "Pérez",
      "full_name": "Juan Pérez",
      "username": "jperez",
      "email": "juan@empresa.com",
      "avatar": null,
      "phone": "123456789",
      "is_active": true,
      "last_login_at": "2024-06-27T09:30:00Z",
      "email_verified_at": null,
      "roles": null,
      "created_at": "2024-06-20T10:00:00Z",
      "updated_at": "2024-06-27T09:30:00Z"
    }
  ]
}
```

---

### Asignar Rol a Usuario
**POST** `/api/roles/{role}/users`

Requiere permiso: `roles.manage`

Asigna el rol al usuario indicado.

**Body:**
```json
{
  "user_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Rol asignado al usuario correctamente.",
  "data": {
    "id": 8,
    "name": "Editor",
    "is_system": false,
    "permissions": ["projects.view", "tasks.view", "tasks.create"],
    "permissions_count": 3,
    "users_count": 1,
    "created_at": "2024-06-27T10:30:00Z"
  }
}
```

---

### Revocar Rol de Usuario
**DELETE** `/api/roles/{role}/users/{user}`

Requiere permiso: `roles.manage`

Elimina el rol del usuario indicado.

**Response (200):**
```json
{
  "success": true,
  "message": "Rol revocado del usuario correctamente.",
  "data": null
}
```

---

## Permisos

### Listar Permisos
**GET** `/api/permissions`

Requiere permiso: `roles.manage` o `permissions.manage`

**Response (200):**
```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "name": "projects.view",
      "module": "projects",
      "roles_count": 5
    },
    {
      "id": 2,
      "name": "projects.create",
      "module": "projects",
      "roles_count": 3
    }
  ]
}
```

---

### Listar Permisos Agrupados por Módulo
**GET** `/api/permissions/grouped`

Requiere permiso: `roles.manage` o `permissions.manage`

**Response (200):**
```json
{
  "success": true,
  "message": null,
  "data": {
    "projects": [
      {
        "id": 1,
        "name": "projects.view",
        "module": "projects",
        "roles_count": 5
      },
      {
        "id": 2,
        "name": "projects.create",
        "module": "projects",
        "roles_count": 3
      }
    ],
    "tasks": [
      {
        "id": 5,
        "name": "tasks.view",
        "module": "tasks",
        "roles_count": 6
      }
    ]
  }
}
```

---

### Crear Permiso
**POST** `/api/permissions`

Requiere permiso: `permissions.manage`

**Body:**
```json
{
  "name": "invoices.view"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Permiso creado correctamente.",
  "data": {
    "id": 18,
    "name": "invoices.view",
    "module": "invoices",
    "roles_count": 0
  }
}
```

---

### Actualizar Permiso
**PUT/PATCH** `/api/permissions/{permission}`

Requiere permiso: `permissions.manage`

**Body:**
```json
{
  "name": "invoices.manage"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Permiso actualizado correctamente.",
  "data": {
    "id": 18,
    "name": "invoices.manage",
    "module": "invoices",
    "roles_count": 0
  }
}
```

---

### Eliminar Permiso
**DELETE** `/api/permissions/{permission}`

Requiere permiso: `permissions.manage`

**Response (200):**
```json
{
  "success": true,
  "message": "Permiso eliminado correctamente.",
  "data": null
}
```

---

## Usuarios

### Listar Roles de Usuario
**GET** `/api/users/{user}/roles`

Requiere: Usuario autenticado (puede ver sus propios roles o tener permiso `users.view`)

Devuelve los nombres de todos los roles asignados al usuario.

**Response (200):**
```json
{
  "success": true,
  "message": null,
  "data": [
    "Administrador",
    "Project Manager"
  ]
}
```

---

### Listar Permisos Efectivos de Usuario
**GET** `/api/users/{user}/permissions`

Requiere: Usuario autenticado (puede ver sus propios permisos o tener permiso `users.view`)

Devuelve todos los permisos efectivos del usuario, incluidos los heredados de sus roles.

**Response (200):**
```json
{
  "success": true,
  "message": null,
  "data": [
    "projects.view",
    "projects.create",
    "projects.edit",
    "projects.delete",
    "tasks.view",
    "tasks.create",
    "tasks.edit",
    "tasks.delete",
    "users.view",
    "roles.manage",
    "reports.view",
    "wiki.manage",
    "time.manage"
  ]
}
```

---

## Errores Comunes

### 401 Unauthorized (No autenticado)
```json
{
  "message": "Unauthenticated.",
  "exception": "..."
}
```

### 403 Forbidden (Sin permiso)
```json
{
  "success": false,
  "message": "Este usuario no está autorizado para realizar esta acción.",
  "errors": null
}
```

### 404 Not Found
```json
{
  "message": "No query results found for model [App\\Models\\User]",
  "exception": "..."
}
```

### 422 Unprocessable Entity (Validación)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["El campo name es requerido."]
  }
}
```

---

## Permisos del Sistema

| Permiso | Descripción |
|---------|-------------|
| `projects.view` | Ver proyectos |
| `projects.create` | Crear proyectos |
| `projects.edit` | Editar proyectos |
| `projects.delete` | Eliminar proyectos |
| `tasks.view` | Ver tareas |
| `tasks.create` | Crear tareas |
| `tasks.edit` | Editar tareas |
| `tasks.delete` | Eliminar tareas |
| `users.view` | Ver usuarios |
| `users.create` | Crear usuarios |
| `users.edit` | Editar usuarios |
| `users.delete` | Eliminar usuarios |
| `roles.manage` | Gestionar roles |
| `permissions.manage` | Gestionar permisos |
| `reports.view` | Ver reportes |
| `wiki.manage` | Gestionar wiki |
| `time.manage` | Gestionar registro de horas |

---

## Roles Predefinidos

| Rol | Sistema | Permisos |
|-----|---------|----------|
| Super Administrador | ✅ Sí | Todos (17 permisos) |
| Administrador | ✅ Sí | Todos excepto `roles.manage`, `permissions.manage` |
| Project Manager | ❌ No | projects.view, projects.edit, tasks.*, users.view, reports.view, wiki.manage, time.manage |
| Desarrollador | ❌ No | projects.view, tasks.view, tasks.create, tasks.edit, wiki.manage, time.manage |
| QA | ❌ No | projects.view, tasks.view, tasks.edit, time.manage |
| Cliente | ❌ No | projects.view, tasks.view, reports.view |
| Invitado | ❌ No | projects.view, tasks.view |

---

## Notas de Implementación

- **Roles del sistema**: `Super Administrador` y `Administrador` no pueden ser eliminados ni renombrados. Intentar hacerlo devuelve HTTP 422.
- **UUID vs ID**: Los usuarios usan UUID (el campo `id` en las respuestas es el UUID). Los roles y permisos usan ID entero de Spatie.
- **Permisos efectivos**: Incluyen permisos directos + permisos heredados de roles.
- **Conteos**: Los conteos (`permissions_count`, `users_count`, `roles_count`) se cargan automáticamente cuando se solicitan.
- **Guard name**: Todos los roles y permisos usan `guard_name = 'web'`.
