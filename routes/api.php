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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;

// Tenant Controllers
use App\Http\Controllers\tenant\AuthController as TenantAuthController;
use App\Http\Controllers\tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\tenant\RequestController;
use App\Http\Controllers\tenant\SettingsController;
use App\Http\Controllers\tenant\UserController as TenantUserController;
use App\Http\Controllers\tenant\FileController;
use App\Http\Controllers\tenant\FileCollectionController;
use App\Http\Controllers\tenant\CollectionController;
use App\Http\Controllers\tenant\DocumentController;
use App\Http\Controllers\tenant\EmailSubmissionController;
use App\Http\Controllers\tenant\AnalyticsController;
use App\Http\Controllers\tenant\PageController;
use App\Http\Controllers\tenant\ItemController;

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

// Route::group([
//   'prefix' => '/tenant/{tenant}',
//   'middleware' => [InitializeTenancyByPath::class],
// ], function () {

//   Route::prefix('auth')->middleware([LogRequestResponse::class])->group(function () {
//     Route::post('/login', [TenantAuthController::class, 'login']);

//     Route::post('/register', [TenantAuthController::class, 'register']);
//     Route::get('/register/confirm/{email}/{verification_code}', [TenantAuthController::class, 'registerConfirm']);

//     Route::post('/password_reset', [TenantAuthController::class, 'passwordReset']);
//     Route::get('/password_reset/confirm/{email}/{verification_code}', [TenantAuthController::class, 'passwordResetConfirm']);

//     Route::post('/email_change', [TenantAuthController::class, 'emailChange']);
//     Route::get('/email_change/confirm/old/{email}/{verification_code}', [TenantAuthController::class, 'emailChangeConfirmOld']);
//     Route::get('/email_change/confirm/new/{email}/{verification_code}', [TenantAuthController::class, 'emailChangeConfirmNew']);
//   });

//   Route::prefix('dashboard')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
//     Route::get('/', [TenantDashboardController::class, 'index']);
//   });

//   Route::prefix('request')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
//     Route::get('/', [RequestController::class, 'index']);
//     Route::delete('/', [RequestController::class, 'clear']);
//   });

//   Route::prefix('analytics')->middleware([])->group(function () {
//     Route::post('/request', [AnalyticsController::class, 'client_request']);

//     Route::prefix('/')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
//       Route::get('/', [AnalyticsController::class, 'index']);
//     });

//     Route::delete('/', [AnalyticsController::class, 'clear']);
//   });

//   Route::prefix('setting')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
//     Route::get('/', [SettingsController::class, 'index']);
//     Route::patch('/{requestKey}', [SettingsController::class, 'update']);
//   });

//   Route::prefix('user')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
//     Route::get('/', [TenantUserController::class, 'index']);
//     Route::get('/{user}', [TenantUserController::class, 'show']);
//     Route::post('/', [TenantUserController::class, 'create']);
//     Route::put('/{user}', [TenantUserController::class, 'update']);
//     Route::patch('/{user}', [TenantUserController::class, 'update']);
//     Route::delete('/{user}', [TenantUserController::class, 'destroy']);
//   });

//   Route::prefix('collection')->group(function () {
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/', [CollectionController::class, 'index']);
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{collectionName}', [CollectionController::class, 'show']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/', [CollectionController::class, 'store']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->patch('/{collection}', [CollectionController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->put('/{collection}', [CollectionController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{collection}', [CollectionController::class, 'destroy']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{collection}/field', [CollectionController::class, 'addField']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{collection}/field/{field}', [CollectionController::class, 'removeField']);

//     // Documents
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{collectionName}/document', [DocumentController::class, 'index']);
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{collectionName}/document/{documentId}', [DocumentController::class, 'show']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/{collectionName}/document', [DocumentController::class, 'store']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->patch('/{collectionName}/document/{documentId}', [DocumentController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->put('/{collectionName}/document/{documentId}', [DocumentController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{collectionName}/document/{documentId}', [DocumentController::class, 'destroy']);
//   });

//   Route::prefix('page')->group(function () {
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/', [PageController::class, 'index']);
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{pageName}', [PageController::class, 'show']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/', [PageController::class, 'store']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->patch('/{page}', [PageController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->put('/{page}', [PageController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{page}', [PageController::class, 'destroy']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{page}/field', [PageController::class, 'addField']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{page}/field/{field}', [PageController::class, 'removeField']);
//   });

//   Route::prefix('item')->group(function () {
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/', [ItemController::class, 'index']);
//     Route::middleware([AuthenticateTokenTenantOptional::class, LogRequestResponse::class])->get('/{itemName}', [ItemController::class, 'show']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/', [ItemController::class, 'store']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->patch('/{item}', [ItemController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->put('/{item}', [ItemController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{item}', [ItemController::class, 'destroy']);
//   });

//   Route::prefix('collection_field_type')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
//     Route::get('/', [CollectionController::class, 'getFieldTypes']);
//     Route::post('/', [CollectionController::class, 'createFieldType']);
//   });

//   Route::prefix('file')->group(function () {
//     Route::middleware([LogRequestResponse::class])->get('/', [FileController::class, 'index']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/', [FileController::class, 'upload']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/bulk', [FileController::class, 'uploadBulk']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->put('/{file}', [FileController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->patch('/{file}', [FileController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{file}', [FileController::class, 'destroy']);

//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/{file}/collection', [FileController::class, 'attachCollections']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->patch('/{file}/collection', [FileController::class, 'syncCollections']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{file}/collection', [FileController::class, 'detachCollections']);

//     Route::middleware([LogRequestResponse::class])->get('/{fileName}', [FileController::class, 'retrieveFile']);
//   });

//   Route::prefix('file_collection')->group(function () {
//     Route::middleware([LogRequestResponse::class])->get('/', [FileCollectionController::class, 'index']);
//     Route::middleware([LogRequestResponse::class])->get('/{collection}', [FileCollectionController::class, 'show']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/', [FileCollectionController::class, 'create']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->put('/{collection}', [FileCollectionController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->patch('/{collection}', [FileCollectionController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{collection}', [FileCollectionController::class, 'destroy']);

//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->post('/{collection}/file/{file}', [FileCollectionController::class, 'addFiles']);
//     Route::middleware([AuthenticateTokenTenant::class, LogRequestResponse::class])->delete('/{collection}/file/{file}', [FileCollectionController::class, 'removeFile']);
//   });

//   Route::prefix('email_submission')->group(function () {
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->get('/', [EmailSubmissionController::class, 'index']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/', [EmailSubmissionController::class, 'store']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->patch('/{emailSubmission}', [EmailSubmissionController::class, 'update']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{emailSubmission}', [EmailSubmissionController::class, 'destroy']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{emailSubmission}/field', [EmailSubmissionController::class, 'addField']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{emailSubmission}/field/{field}', [EmailSubmissionController::class, 'removeField']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{emailSubmission}/recipient', [EmailSubmissionController::class, 'addRecipient']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->post('/{emailSubmission}/recipient/sync', [EmailSubmissionController::class, 'syncRecipient']);
//     Route::middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->delete('/{emailSubmission}/recipient/{user}', [EmailSubmissionController::class, 'removeRecipient']);

//     Route::middleware([LogRequestResponse::class])->post('/{emailSubmissionName}/submit', [EmailSubmissionController::class, 'submit']);
//   });
// });

Route::prefix('admin')->group(function () {
  // Admin routes
  Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register/confirm/{email}/{verification_code}', [AuthController::class, 'registerConfirm']);

    Route::post('/password_reset', [AuthController::class, 'passwordReset']);
    Route::get('/password_reset/confirm/{email}/{verification_code}', [AuthController::class, 'passwordResetConfirm']);

    Route::post('/email_change', [AuthController::class, 'emailChange']);
    Route::get('/email_change/confirm/old/{email}/{verification_code}', [AuthController::class, 'emailChangeConfirmOld']);
    Route::get('/email_change/confirm/new/{email}/{verification_code}', [AuthController::class, 'emailChangeConfirmNew']);
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
    
    Route::post('/{tenant}/user', [TenantController::class, 'createTenantUser']);
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
});