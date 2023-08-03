<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Middleware
use App\Http\Middleware\AuthenticateToken;

// Controllers
use App\Http\Controllers\AuthController;

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
