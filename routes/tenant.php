<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

// Middleware
use App\Http\Middleware\AuthenticateToken;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\tenant\FileController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
  InitializeTenancyByDomain::class,
  PreventAccessFromCentralDomains::class,
  'api'
])->group(function () {

  Route::middleware([AuthenticateToken::class])->post('/test', function(Request $request){
    $currentTenant = tenant();
    return "This is a test! {$currentTenant->id}";
  });

  Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
  });

  Route::prefix('file')->group(function () {
    Route::get('/', [FileController::class, 'index']);
    Route::post('/', [FileController::class, 'upload']);
    Route::put('/{fileName}', [FileController::class, 'update']);
    Route::patch('/{fileName}', [FileController::class, 'update']);

    Route::get('/{fileName}', [FileController::class, 'retrieveFile']);
    // Route::get('/{collection}/{fileName}', [FileController::class, 'retrieveFileByCollection']);
    Route::delete('/{fileName}', [FileController::class, 'destroyFile']);
    // Route::delete('/{collection}/{fileName}', [FileController::class, 'destroyFileByCollection']);
  });

});
