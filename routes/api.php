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

    Route::get('/centros', function (Illuminate\Http\Request $request) {
        $path = public_path('data/centros_venezuela.json');
        if (!file_exists($path)) {
            return response()->json(['success' => false, 'message' => 'No se encontraron centros.'], 404);
        }
        $data = json_decode(file_get_contents($path), true);

        $query = strtolower(trim($request->query('query', '')));
        $city = trim($request->query('city', ''));
        $type = trim($request->query('type', ''));

        $filtered = array_values(array_filter($data, function ($item) use ($query, $city, $type) {
            if ($query) {
                $nameMatch = isset($item['name']) && str_contains(strtolower($item['name']), $query);
                $addressMatch = isset($item['address']) && str_contains(strtolower($item['address']), $query);
                $cityMatch = isset($item['city']) && str_contains(strtolower($item['city']), $query);
                if (!$nameMatch && !$addressMatch && !$cityMatch) {
                    return false;
                }
            }

            if ($city && (!isset($item['city']) || strcasecmp($item['city'], $city) !== 0)) {
                return false;
            }

            if ($type && (!isset($item['type']) || strcasecmp($item['type'], $type) !== 0)) {
                return false;
            }

            return true;
        }));

        return response()->json([
            'success' => true,
            'total' => count($filtered),
            'centros' => $filtered
        ]);
    })->middleware('throttle:api-search')->name('centros');

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
