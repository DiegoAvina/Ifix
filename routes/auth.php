<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // El 3er parámetro (prefijo) es obligatorio aquí: sin él, la firma
    // por defecto de throttle:N,1 es solo `$route->getDomain().'|'.$ip`
    // — SIN nada específico de la ruta (getDomain() es null, no hay
    // restricción de dominio en estas rutas) — así que register,
    // forgot-password y reset-password compartirían el MISMO contador
    // entre sí si no se distinguen. Lo confirmé probándolo: sin el
    // prefijo, agotar el límite de una ruta agotaba también las otras dos.
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:6,1,register');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // POST login NO lleva throttle aquí a propósito: LoginRequest ya
    // aplica su propio rate limiting (5 intentos por combinación
    // email|ip, ver ensureIsNotRateLimited()) desde antes de esta
    // tarea. Agregar throttle:N,1 encima solo duplicaría el límite
    // sin que quede claro cuál de los dos está actuando.
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Más estricto que register/reset: dispara un envío de correo, y
    // ya existe un cooldown de 60s POR EMAIL en el password broker de
    // Laravel (config/auth.php: passwords.users.throttle) — este
    // throttle:3,1 es la capa POR IP que faltaba encima de eso
    // (cubre a alguien rotando emails distintos, que el broker no ve).
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:3,1,forgot-password')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:5,1,reset-password')
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
