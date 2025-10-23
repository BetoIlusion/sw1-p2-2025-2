<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagramaController;
use App\Models\User;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [DiagramaController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/compartidos', [DiagramaController::class, 'compartidos'])->name('dashboard.compartidos');


    Route::prefix('/diagramas')->group(function () {
        Route::get('/uml', [DiagramaController::class, 'uml'])->name('uml.show');
        Route::post('/reporte-diagrama', [DiagramaController::class, 'diagramaReporte'])
            ->name('diagrama-reporte.create');
        Route::post('/', [DiagramaController::class, 'store'])->name('diagramas.store');
        Route::get('/{diagrama}', [DiagramaController::class, 'show'])->name('diagramas.show');
        Route::post('/{diagrama}/contenido', [DiagramaController::class, 'updateContenido'])->name('diagramas.updateContenido');
        Route::get('/descarga', [DiagramaController::class, 'download'])->name('diagrama.download');
        Route::delete('/{id}', [DiagramaController::class, 'destroy'])->name('diagramas.destroy');
        Route::get('/exportar-spring-boot/{id}', [DiagramaController::class, 'exportSpringboot'])
            ->name('diagramas.exportarSpringBoot');
        Route::get('/{diagrama}/compartir', [DiagramaController::class, 'compartirDiagrama'])->name('diagramas.compartir');
        Route::post('/update-with-ai', [DiagramaController::class, 'updateWithAI'])->name('diagramas.updateWithAI');

        Route::post('/analizar-imagen', [DiagramaController::class, 'procesarImagen'])
            ->name('analizar.imagen');
    });

    Route::get('/prueba', function () {
        $usuarios = User::all();

        return $usuarios;
    })->name('prueba');

});
