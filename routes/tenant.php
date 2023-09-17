<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

// Middleware
use App\Http\Middleware\AuthenticateToken;
use App\Http\Middleware\AuthenticateTokenTenant;
use App\Http\Middleware\AdminUserOnlyTenant;
use App\Http\Middleware\PublicUserOnlyTenant;

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

  Route::middleware([AuthenticateTokenTenant::class])->post('/test', function(Request $request){
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

  Route::prefix('file')->middleware([AuthenticateTokenTenant::class])->group(function () {
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
    Route::get('/{collectionName}', [CollectionController::class, 'show']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class])->post('/', [CollectionController::class, 'store']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class])->delete('/{collectionName}', [CollectionController::class, 'destroy']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class])->post('/{collectionName}/field', [CollectionController::class, 'addField']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class])->delete('/{collectionName}/field/{fieldName}', [CollectionController::class, 'removeField']);

    // Documents
    Route::get('/{collectionName}/document', [DocumentController::class, 'index']);
    Route::get('/{collectionName}/document/{documentId}', [DocumentController::class, 'show']);
    Route::middleware([AuthenticateTokenTenant::class])->post('/{collectionName}/document', [DocumentController::class, 'store']);
    Route::middleware([AuthenticateTokenTenant::class])->patch('/{collectionName}/document/{documentId}', [DocumentController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class])->delete('/{collectionName}/document/{documentId}', [DocumentController::class, 'destroy']);
  });

  Route::prefix('collection_field_type')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class])->group(function () {
    Route::get('/', [CollectionController::class, 'getFieldTypes']);
    Route::post('/', [CollectionController::class, 'createFieldType']);
  });

});
