<?php

return [

    'models' => [

        /*
         * Modelo Permission usado por el paquete.
         */
        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * Modelo Role usado por el paquete.
         */
        'role' => App\Models\Role::class,

    ],

    'table_names' => [

        'roles' => 'roles',

        'permissions' => 'permissions',

        'model_has_permissions' => 'model_has_permissions',

        'model_has_roles' => 'model_has_roles',

        'role_has_permissions' => 'role_has_permissions',

    ],

    'column_names' => [
        // Nuestros modelos usan bigIncrement id como PK interna (la columna
        // uuid es solo para exposicion publica), por lo que el morph key
        // estandar de Spatie (model_id / model_type) funciona sin cambios.
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'events_enabled' => true,

    'teams' => false,

    'display_permission_in_exception' => true,

    'display_role_in_exception' => true,

    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],

];
