<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DesaparecidoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::name('api.')->group(function () {
    Route::get('/buscar', [DesaparecidoController::class, 'search'])
        ->middleware('throttle:api-search')
        ->name('buscar');

    Route::get('/sincronizar-busqueda', [DesaparecidoController::class, 'syncSearch'])
        ->middleware('throttle:api-sync')
        ->name('sincronizar-busqueda');

    Route::post('/buscar-foto', [DesaparecidoController::class, 'searchByPhoto'])
        ->middleware('throttle:api-photo')
        ->name('buscar-foto');

    Route::post('/reportar', [DesaparecidoController::class, 'store'])
        ->middleware('throttle:api-write')
        ->name('reportar');

    Route::post('/caso/{id}/localizado', [DesaparecidoController::class, 'markFound'])
        ->whereNumber('id')
        ->middleware('throttle:api-write')
        ->name('caso.localizado');
});
