<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Esta aplicacion es una API pura. Esta ruta solo confirma que el backend
| esta funcionando cuando se accede directamente desde el navegador.
|
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Project Manager API - ver documentacion en /api',
        'data' => null,
    ]);
});
