<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AmountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;

// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas Protegidas (Requieren enviar el token en el header)
Route::middleware('auth:sanctum')->group(function () {
    
    // Obtener los datos del usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role');
    });

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de Gestión de Empleados
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

    // Rutas de Gestión de Productos
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Rutas de Gestión de Clientes
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::put('/clients/{id}', [ClientController::class, 'update']);
    Route::delete('/clients/{id}', [ClientController::class, 'destroy']);

    // Rutas de Gestión de Montos
    Route::get('/amounts', [AmountController::class, 'index']);
    Route::post('/amounts', [AmountController::class, 'store']);
    Route::put('/amounts/{id}', [AmountController::class, 'update']);
    Route::delete('/amounts/{id}', [AmountController::class, 'destroy']);

    // Rutas de Gestión de Pagos
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::patch('/payments/{id}/accept', [PaymentController::class, 'acceptPayment']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);

    // Rutas de Notificaciones
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Rutas de Dashboards y Analíticas
    Route::get('/dashboard/admin/kpis', [DashboardController::class, 'getAdminKpis']);
    Route::get('/dashboard/admin/top-barbers', [DashboardController::class, 'getTopBarbers']);
    Route::get('/dashboard/admin/top-products', [DashboardController::class, 'getTopProducts']);
    Route::get('/dashboard/admin/revenue-chart', [DashboardController::class, 'getRevenueChart']);

    // --- Dashboard del Empleado ---
    Route::get('/dashboard/barber/my-kpis', [DashboardController::class, 'getBarberKpis']);
    Route::get('/dashboard/barber/my-chart', [DashboardController::class, 'getBarberChart']);
    
});