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
use App\Http\Controllers\tenant\DashboardController;
use App\Http\Controllers\tenant\UserController;
use App\Http\Controllers\tenant\FileController;
use App\Http\Controllers\tenant\CollectionController;
use App\Http\Controllers\tenant\DocumentController;

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

  Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
  });

  Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{user}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'create']);
    Route::put('/{user}', [UserController::class, 'update']);
    Route::patch('/{user}', [UserController::class, 'update']);
    Route::delete('/{user}', [UserController::class, 'destroy']);
  
    Route::post('/{user}/verify', [UserController::class, 'verifyEmail']);
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

  Route::prefix('collection')->group(function () {
    Route::get('/', [CollectionController::class, 'index']);
    Route::get('/{collection}', [CollectionController::class, 'show']);
    Route::post('/', [CollectionController::class, 'store']);
    Route::delete('/{collection}', [CollectionController::class, 'destroy']);
    Route::post('/{collection}/field', [CollectionController::class, 'addField']);
    Route::delete('/{collection}/field/{fieldName}', [CollectionController::class, 'removeField']);

    // Documents
    Route::get('/{collection}/document', [DocumentController::class, 'index']);
    Route::get('/{collection}/document/{document}', [DocumentController::class, 'show']);
    Route::post('/{collection}/document', [DocumentController::class, 'store']);
    Route::patch('/{collection}/document/{document}', [DocumentController::class, 'update']);
    Route::delete('/{collection}/document/{document}', [DocumentController::class, 'destroy']);
  });

  Route::prefix('collection_field_type')->group(function () {
    Route::get('/', [CollectionController::class, 'getFieldTypes']);
    Route::post('/', [CollectionController::class, 'createFieldType']);
  });

});
