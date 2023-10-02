<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Middleware
use App\Http\Middleware\AuthenticateToken;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;

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

Route::middleware(AuthenticateToken::class)->post('/test', function(Request $request){
  return "This is a test! {$request->email}";
});

Route::prefix('auth')->group(function () {
  Route::post('/login', [AuthController::class, 'login']);

  Route::post('/register/confirm', [AuthController::class, 'registerConfirm']);

  Route::post('/password_reset', [AuthController::class, 'passwordReset']);
  Route::post('/password_reset/confirm', [AuthController::class, 'passwordResetConfirm']);

  Route::post('/email_change', [AuthController::class, 'emailChange']);
  Route::post('/email_change/confirm/old', [AuthController::class, 'emailChangeConfirmOld']);
  Route::post('/email_change/confirm/new', [AuthController::class, 'emailChangeConfirmNew']);
});

Route::prefix('dashboard')->middleware(AuthenticateToken::class)->group(function () {
  Route::get('/', [DashboardController::class, 'index']);
});

Route::prefix('tenant')->middleware(AuthenticateToken::class)->group(function () {
  Route::get('/', [TenantController::class, 'index']);
  Route::get('/{tenant}', [TenantController::class, 'show']);
  Route::post('/', [TenantController::class, 'create']);
  Route::put('/{tenant}', [TenantController::class, 'update']);
  Route::patch('/{tenant}', [TenantController::class, 'update']);
  Route::delete('/{tenant}', [TenantController::class, 'destroy']);
});

Route::prefix('user')->middleware(AuthenticateToken::class)->group(function () {
  Route::get('/', [UserController::class, 'index']);
  Route::get('/{user}', [UserController::class, 'show']);
  Route::post('/', [UserController::class, 'create']);
  Route::put('/{user}', [UserController::class, 'update']);
  Route::patch('/{user}', [UserController::class, 'update']);
  Route::delete('/{user}', [UserController::class, 'destroy']);

  // Forgot password
  // Verify forgotten password
});
