<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Exception;

use App\Models\User;
use App\Models\Tenant;
use App\Models\tenant\FieldType;
use App\Models\tenant\Setting;

class TenantController extends Controller
{

  protected function rrmdir($dir)
  {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
            $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
          else
            unlink($dir . DIRECTORY_SEPARATOR . $object);
        }
      }
      rmdir($dir);
    }
  }

  public function index(Request $request)
  {
    try {

      $query = Tenant::query();
      $query->orderBy('created_at', 'desc');

      $tenants = $query->get();

      return response([
        'message' => 'List all tenants.',
        'tenants' => $tenants
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show(Request $request, Tenant $tenant)
  {
    try {
      return response([
        'message' => 'Tenant record.',
        'tenant' => $tenant
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function create(Request $request)
  {

    $request->validate([
      'name' => ['required'],
      'storage_limit_file' => ['required'],
      'storage_limit_database' => ['required'],
      'email' => ['required'],
      'password' => ['required'],
    ]);

    try {

      $tenantName = Str::of($request->name)->slug();
      $adminEmail = $request->email;
      $adminPassword = $request->password;

      // Create tenant
      $tenant = new Tenant;
      $tenant->id = Str::of($request->name)->slug();
      $tenant->storage_limit_file = $request->storage_limit_file;
      $tenant->storage_limit_database = $request->storage_limit_database;
      $tenant->save();

      // Create domains
      // foreach(config('tenancy.central_domains') as $centralDomain){
      //   $tenant->domains()->create(['domain' => "{$tenantName}.{$centralDomain}"]);
      // }

      // Create tenant folder
      mkdir(storage_path("tenant-{$tenant->id}"));

      // Create tenant admin user
      $tenant->run(function (Tenant $tenant) use ($tenantName, $adminEmail, $adminPassword) {
        $user = User::create([
          'name' => 'Admin',
          'email' => $adminEmail,
          'password' => bcrypt($adminPassword),
          'public' => false,
          'blocked' => false
        ]);

        $user->admin = true;

        $user->email_verified_at = now();
        $user->remember_token = Str::random(10);

        $user->save();
      });

      // Seed tenant data
      $tenant->run(function (Tenant $tenant) {

        foreach (config("cmsbase.collection_types") as $index => $type) {
          $fieldType = FieldType::create([
            'name' => $index,
            'datatype' => $type
          ]);
        }

        foreach (config("cmsbase.default_settings") as $index => $type) {
          $setting = Setting::create([
            'key' => $index,
            'value' => $type
          ]);
        }
      });

      return response([
        'message' => 'Tenant created.',
        'tenant' => $tenant
      ], 200);
    } catch (QueryException $e) {

      if ($e->getCode() == 23505) {
        return response([
          'message' => 'A file with this name already exists.'
        ], 409);
      }

      return response([
        'message' => $e->getMessage(),
        'error' => $e
      ], 500);
    }
  }

  public function createTenantUser(Request $request, Tenant $tenant)
  {

    $request->validate([
      'name' => ['required'],
      'email' => ['required'],
      'password' => ['required'],
      'admin' => ['required'],
    ]);

    try {

      $tenant->run(function (Tenant $tenant) use ($request) {
        $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => bcrypt($request->password),
          'public' => false,
          'blocked' => false
        ]);

        $user->admin = $request->admin;

        $user->email_verified_at = now();
        $user->remember_token = Str::random(10);

        $user->save();
      });

      return response([
        'message' => 'Updated tenant.',
        'tenant' => $tenant
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, Tenant $tenant)
  {
    try {

      if ($request->has('storage_limit_file')) {
        $tenant->storage_limit_file = (int) $request->storage_limit_file;
      }

      if ($request->has('storage_limit_database')) {
        $tenant->storage_limit_database = (int) $request->storage_limit_database;
      }

      if ($request->has('disabled')) {
        $tenant->disabled = boolval($request->disabled);
      }

      $tenant->save();

      return response([
        'message' => 'Updated tenant.',
        'tenant' => $tenant
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Request $request, Tenant $tenant)
  {
    try {

      $this->rrmdir(base_path() . "/storage/tenant-{$tenant->id}");
      $this->rrmdir(base_path() . "/storage/app/public/tenant/{$tenant->id}");
      $tenant->delete();

      return response([
        'message' => 'Tenant removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
