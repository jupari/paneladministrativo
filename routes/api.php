<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\WorkshopsController;
use App\Http\Controllers\Api\V1\ProductionOrdersController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\OperationsBulkController;
use App\Http\Controllers\Api\V1\DamagedGarmentsBulkController;
use App\Http\Controllers\Api\V1\EvidencesController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\WorkshopPairingController;
use App\Models\ProductionOrder;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
*/

// ── Ruta temporal de mantenimiento (BORRAR después de usar) ──────────────
Route::get('maintenance/recalculate-units', function () {
    $orders = ProductionOrder::whereNull('deleted_at')->get();
    $updated = 0;
    $details = [];

    foreach ($orders as $order) {
        $before = (int) $order->completed_units;
        $order->recalculateCompletedUnits();
        $order->refresh();
        $after = (int) $order->completed_units;

        if ($before !== $after) {
            $details[] = "Orden #{$order->id} ({$order->order_code}): {$before} → {$after}";
            $updated++;
        }
    }

    return response()->json([
        'message' => "Recalculación completada. {$updated} órdenes actualizadas de {$orders->count()} totales.",
        'details' => $details,
    ]);
});

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Auth ────────────────────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login',   [AuthController::class, 'login'])->name('login');
        Route::post('refresh', [AuthController::class, 'refresh'])
             ->middleware('auth.refresh')
             ->name('refresh');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('me',      [AuthController::class, 'me'])->name('me');
        });
    });

    // ── Rutas protegidas ────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // ── Talleres ───────────────────────────────────────────────────
        Route::get('workshops', [WorkshopsController::class, 'index'])->name('workshops.index');
        Route::post('workshops/pair', [WorkshopPairingController::class, 'pair'])->name('workshops.pair');
        Route::get('device/status',    [WorkshopPairingController::class, 'deviceStatus'])->name('device.status');

        Route::prefix('workshops/{workshopId}')
             ->middleware('workshop.access')
             ->name('workshops.')
             ->group(function () {
                 Route::get('/',         [WorkshopsController::class,       'show'])->name('show');
                 Route::get('operators', [WorkshopsController::class,       'operators'])->name('operators');
                 Route::get('orders',    [ProductionOrdersController::class,'index'])
                    ->middleware('workshop.device.enrolled:workshop')
                    ->name('orders.index');
                 Route::get('devices',   [WorkshopPairingController::class, 'indexDevices'])->name('devices.index');
                 Route::patch('devices/{deviceId}/status', [WorkshopPairingController::class, 'updateDeviceStatus'])->name('devices.status');
             });

        // ── Órdenes individuales ───────────────────────────────────────
        Route::prefix('production-orders/{orderId}')
            ->middleware('workshop.device.enrolled:order')
            ->name('orders.')
            ->group(function () {
            Route::get('/',          [ProductionOrdersController::class, 'show'])->name('show');
            Route::get('activities', [ProductionOrdersController::class, 'activities'])->name('activities');
            Route::patch('status',   [ProductionOrdersController::class, 'updateStatus'])->name('status');
        });

        // ── Catálogos ──────────────────────────────────────────────────
        Route::prefix('catalog')->name('catalog.')->group(function () {
            Route::get('activities',   [CatalogController::class, 'activities'])->name('activities');
            Route::get('operators',    [CatalogController::class, 'operators'])->name('operators');
            Route::get('damage-types', [CatalogController::class, 'damageTypes'])->name('damageTypes');
        });

        // ── Sync Bulk ──────────────────────────────────────────────────
        Route::post('operations/bulk',       [OperationsBulkController::class,      'store'])
            ->middleware('workshop.device.enrolled:operations-bulk')
            ->name('operations.bulk');
        Route::post('damaged-garments/bulk', [DamagedGarmentsBulkController::class, 'store'])
            ->middleware('workshop.device.enrolled:damaged-bulk')
            ->name('damagedGarments.bulk');

        // ── Evidencias ─────────────────────────────────────────────────
        Route::post('evidences',        [EvidencesController::class, 'store'])
            ->middleware('workshop.device.enrolled:evidence-store')
            ->name('evidences.store');
        Route::get('evidences/{id}',    [EvidencesController::class, 'show'])
            ->middleware('workshop.device.enrolled:evidence-route')
            ->name('evidences.show');
        Route::delete('evidences/{id}', [EvidencesController::class, 'destroy'])
            ->middleware('workshop.device.enrolled:evidence-route')
            ->name('evidences.destroy');

        // ── Perfil ─────────────────────────────────────────────────────
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',              [ProfileController::class, 'show'])->name('show');
            Route::get('account-status', [ProfileController::class, 'accountStatus'])->name('accountStatus');
            Route::get('sync-status',    [ProfileController::class, 'syncStatus'])->name('syncStatus');
        });
    });
});
