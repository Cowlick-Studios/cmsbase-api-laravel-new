<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Middleware
use App\Http\Middleware\AuthenticateToken;
use App\Http\Middleware\AuthenticateTokenTenant;
use App\Http\Middleware\AuthenticateTokenTenantOptional;
use App\Http\Middleware\AdminUserOnlyTenant;
use App\Http\Middleware\PublicUserOnlyTenant;
use App\Http\Middleware\LogRequestResponse;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

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

// Tenant routes
Route::group([
  'prefix' => '/tenant/{tenant}',
  'middleware' => [InitializeTenancyByPath::class],
], function () {
  Route::prefix('auth')->middleware([LogRequestResponse::class])->group(function () {
    Route::post('/login', [TenantAuthController::class, 'login']);

    Route::post('/register', [TenantAuthController::class, 'register']);
    Route::post('/register/confirm', [TenantAuthController::class, 'registerConfirm']);

    Route::post('/password_reset', [TenantAuthController::class, 'passwordReset']);
    Route::post('/password_reset/confirm', [TenantAuthController::class, 'passwordResetConfirm']);

    Route::post('/email_change', [TenantAuthController::class, 'emailChange']);
    Route::post('/email_change/confirm/old', [TenantAuthController::class, 'emailChangeConfirmOld']);
    Route::post('/email_change/confirm/new', [TenantAuthController::class, 'emailChangeConfirmNew']);
  });

  Route::prefix('dashboard')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [TenantDashboardController::class, 'index']);
  });

  Route::prefix('request')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [RequestController::class, 'index']);
    Route::delete('/', [RequestController::class, 'clear']);
  });

  Route::prefix('analytics')->middleware([])->group(function () {
    Route::post('/request', [AnalyticsController::class, 'client_request']);

    Route::prefix('/')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
      // Route::get('/', [RequestController::class, 'index']);
      // Route::delete('/', [RequestController::class, 'clear']);
    });
  });


  Route::prefix('setting')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [SettingsController::class, 'index']);
    Route::patch('/{requestKey}', [SettingsController::class, 'update']);
  });

  Route::prefix('user')->middleware([AuthenticateTokenTenant::class, AdminUserOnlyTenant::class, LogRequestResponse::class])->group(function () {
    Route::get('/', [TenantUserController::class, 'index']);
    Route::get('/{user}', [TenantUserController::class, 'show']);
    Route::post('/', [TenantUserController::class, 'create']);
    Route::put('/{user}', [TenantUserController::class, 'update']);
    Route::patch('/{user}', [TenantUserController::class, 'update']);
    Route::delete('/{user}', [TenantUserController::class, 'destroy']);
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

// Admin routes
// Route::prefix('admin')->middleware([])->group(function () {
Route::middleware(AuthenticateToken::class)->post('/test', function (Request $request) {
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
// });
