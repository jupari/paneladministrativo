<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\CotizacionRespuestaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/errors-500', function () {
    return view('errors');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'company.license'])->name('dashboard');

Route::middleware(['auth', 'company.license'])->group(function () {
    Route::get('user/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('user/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('user/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Rutas públicas para aprobación/rechazo de cotizaciones por el cliente ──
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/cotizacion/{token}', [CotizacionRespuestaController::class, 'mostrar'])
        ->name('public.cotizacion.respuesta');
    Route::post('/cotizacion/{token}/responder', [CotizacionRespuestaController::class, 'responder'])
        ->name('public.cotizacion.responder');
});

require __DIR__.'/auth.php';
