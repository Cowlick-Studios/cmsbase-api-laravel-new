<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Middleware
use App\Http\Middleware\AuthenticateToken;

// Controllers
use App\Http\Controllers\AuthController;
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
});

Route::prefix('tenant')->group(function () {
  Route::get('/', [TenantController::class, 'index']);
  Route::get('/{tenant}', [TenantController::class, 'show']);
  Route::post('/', [TenantController::class, 'create']);
  Route::put('/{tenant}', [TenantController::class, 'update']);
  Route::patch('/{tenant}', [TenantController::class, 'update']);
  Route::delete('/{tenant}', [TenantController::class, 'destroy']);
});

Route::prefix('user')->group(function () {
  Route::get('/', [UserController::class, 'index']);
  Route::get('/{user}', [UserController::class, 'show']);
  Route::post('/', [UserController::class, 'create']);
  Route::put('/{user}', [UserController::class, 'update']);
  Route::patch('/{user}', [UserController::class, 'update']);
  Route::delete('/{user}', [UserController::class, 'destroy']);

  Route::post('/{user}/verify', [UserController::class, 'verifyEmail']);

  // Forgot password
  // Verify forgotten password
});
