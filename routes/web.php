<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

use App\Http\Controllers\DesaparecidoController;
use App\Http\Controllers\ScrapeController;

// ==========================================
// Rate Limiters
// ==========================================

RateLimiter::for('api-search', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

RateLimiter::for('api-photo', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});

RateLimiter::for('api-sync', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('api-write', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});

RateLimiter::for('scrape', function (Request $request) {
    return Limit::perMinute(3)->by($request->ip());
});

// ==========================================
// Rutas Públicas
// ==========================================

Route::get('/', [DesaparecidoController::class, 'index'])->name('home');

// ==========================================
// Rutas Web de Escritura (con rate limiting)
// ==========================================

Route::post('/reportar', [DesaparecidoController::class, 'store'])
    ->middleware('throttle:api-write')
    ->name('reportar.store');

Route::post('/caso/{id}/localizado', [DesaparecidoController::class, 'markFound'])
    ->whereNumber('id')
    ->middleware('throttle:api-write')
    ->name('caso.localizado');

// ==========================================
// Ruta de Scraping (protegida por token)
// ==========================================

Route::get('/scrape/run', function (Request $request) {
    $token = env('SCRAPE_TOKEN');

    // Si hay token configurado, exigirlo
    if (!empty($token) && $request->input('token') !== $token) {
        abort(403, 'Acceso denegado. Token inválido.');
    }

    return app(ScrapeController::class)->run($request, app(\App\Services\ScraperService::class));
})->middleware('throttle:scrape')->name('scrape.run');

// ==========================================
// Rutas de Administración (Admin Panel)
// ==========================================

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;

Route::prefix('admin')->name('admin.')->group(function () {
    // Rutas protegidas por Autenticación
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // CRUD de Casos de Desaparecidos
        Route::get('/casos', [AdminController::class, 'casosIndex'])->name('casos.index');
        Route::get('/casos/crear', [AdminController::class, 'casosCreate'])->name('casos.create');
        Route::post('/casos/guardar', [AdminController::class, 'casosStore'])->name('casos.store');
        Route::get('/casos/{id}/editar', [AdminController::class, 'casosEdit'])->name('casos.edit');
        Route::post('/casos/{id}/actualizar', [AdminController::class, 'casosUpdate'])->name('casos.update');
        Route::post('/casos/{id}/eliminar', [AdminController::class, 'casosDestroy'])->name('casos.destroy');

        // Importador Excel/CSV
        Route::get('/importar', [AdminController::class, 'importarIndex'])->name('importar.index');
        Route::post('/importar', [AdminController::class, 'importarPost'])->name('importar.post');
        Route::get('/importar/plantilla', [AdminController::class, 'importarPlantilla'])->name('importar.plantilla');

        // Perfil y Cambio de Contraseña
        Route::get('/perfil', [AdminController::class, 'perfilShow'])->name('perfil');
        Route::post('/perfil/password', [AdminController::class, 'perfilUpdatePassword'])->name('perfil.password');

        // Scraper de Google Drive (Listados de Hospitales)
        Route::get('/scrape-drive/run', [\App\Http\Controllers\Admin\AdminScrapeDriveController::class, 'run'])->name('scrape-drive.run');
    });
});

// Rutas de Login (fuera del grupo con prefijo name para llamarse exactamente 'login')
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login.post');
});

// Documentación interactiva de la API
Route::get('/api-docs', function () {
    return view('api.docs');
})->name('api-docs');



