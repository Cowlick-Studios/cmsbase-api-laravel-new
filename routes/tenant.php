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
use App\Http\Middleware\AuthenticateTokenTenantOptional;
use App\Http\Middleware\AdminUserOnlyTenant;
use App\Http\Middleware\PublicUserOnlyTenant;
use App\Http\Middleware\LogRequestResponse;

// Controllers
use App\Http\Controllers\tenant\AuthController;
use App\Http\Controllers\tenant\DashboardController;
use App\Http\Controllers\tenant\RequestController;
use App\Http\Controllers\tenant\SettingsController;
use App\Http\Controllers\tenant\UserController;
use App\Http\Controllers\tenant\FileController;
use App\Http\Controllers\tenant\FileCollectionController;
use App\Http\Controllers\tenant\CollectionController;
use App\Http\Controllers\tenant\DocumentController;
use App\Http\Controllers\tenant\EmailSubmissionController;

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

  Route::prefix('auth')->middleware([LogRequestResponse::class])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/confirm', [AuthController::class, 'registerConfirm']);

    Route::post('/password_reset', [AuthController::class, 'passwordReset']);
    Route::post('/password_reset/confirm', [AuthController::class, 'passwordResetConfirm']);

    Route::post('/email_change', [AuthController::class, 'emailChange']);
    Route::post('/email_change/confirm/old', [AuthController::class, 'emailChangeConfirmOld']);
    Route::post('/email_change/confirm/new', [AuthController::class, 'emailChangeConfirmNew']);
  });

  Route::prefix('dashboard')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
  });

  Route::prefix('request')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [RequestController::class, 'index']);
    Route::delete('/', [RequestController::class, 'clear']);
  });

  Route::prefix('setting')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [SettingsController::class, 'index']);
    Route::patch('/{requestKey}', [SettingsController::class, 'update']);
  });

  Route::prefix('user')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{user}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'create']);
    Route::put('/{user}', [UserController::class, 'update']);
    Route::patch('/{user}', [UserController::class, 'update']);
    Route::delete('/{user}', [UserController::class, 'destroy']);
  });

  Route::prefix('collection')->group(function () {
    Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/', [CollectionController::class, 'index']);
    Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{collectionName}', [CollectionController::class, 'show']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/', [CollectionController::class, 'store']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->patch('/{collection}', [CollectionController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->put('/{collection}', [CollectionController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{collection}', [CollectionController::class, 'destroy']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{collection}/field', [CollectionController::class, 'addField']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{collection}/field/{field}', [CollectionController::class, 'removeField']);

    // Documents
    Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{collectionName}/document', [DocumentController::class, 'index']);
    Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{collectionName}/document/{documentId}', [DocumentController::class, 'show']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/{collectionName}/document', [DocumentController::class, 'store']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->patch('/{collectionName}/document/{documentId}', [DocumentController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->put('/{collectionName}/document/{documentId}', [DocumentController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{collectionName}/document/{documentId}', [DocumentController::class, 'destroy']);
  });

  Route::prefix('collection_field_type')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [CollectionController::class, 'getFieldTypes']);
    Route::post('/', [CollectionController::class, 'createFieldType']);
  });

  Route::prefix('file')->group(function () {
    Route::middleware([LogRequestResponse::class])->get('/', [FileController::class, 'index']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/', [FileController::class, 'upload']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/bulk', [FileController::class, 'uploadBulk']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->put('/{file}', [FileController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->patch('/{file}', [FileController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{file}', [FileController::class, 'destroy']);

    Route::middleware([LogRequestResponse::class])->get('/{fileName}', [FileController::class, 'retrieveFile']);
  });

  Route::prefix('file_collection')->group(function () {
    Route::middleware([LogRequestResponse::class])->get('/', [FileCollectionController::class, 'index']);
    Route::middleware([LogRequestResponse::class])->get('/{collection}', [FileCollectionController::class, 'show']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/', [FileCollectionController::class, 'create']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->put('/{collection}', [FileCollectionController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->patch('/{collection}', [FileCollectionController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{collection}', [FileCollectionController::class, 'destroy']);

    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/{collection}/file', [FileCollectionController::class, 'addFiles']);
    Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{collection}/file/{file}', [FileCollectionController::class, 'removeFile']);
  });

  Route::prefix('email_submission')->group(function () {
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->get('/', [EmailSubmissionController::class, 'index']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/', [EmailSubmissionController::class, 'store']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->patch('/{emailSubmission}', [EmailSubmissionController::class, 'update']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{emailSubmission}', [EmailSubmissionController::class, 'destroy']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{emailSubmission}/field', [EmailSubmissionController::class, 'addField']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{emailSubmission}/field/{field}', [EmailSubmissionController::class, 'removeField']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{emailSubmission}/recipient', [EmailSubmissionController::class, 'addRecipient']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{emailSubmission}/recipient/sync', [EmailSubmissionController::class, 'syncRecipient']);
    Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{emailSubmission}/recipient/{user}', [EmailSubmissionController::class, 'removeRecipient']);

    Route::middleware([LogRequestResponse::class])->post('/{emailSubmissionName}/submit', [EmailSubmissionController::class, 'submit']);
  });
  
});
