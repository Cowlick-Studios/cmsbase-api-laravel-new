<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Tenant;
use App\Models\tenant\CollectionFieldType;
use App\Models\tenant\Setting;

class CreateNewTenant extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'tenant:create {newTenantName} {newAdminEmail} {newAdminPassword}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates a new tenant in the system with admin user.';

  /**
   * Execute the console command.
   */
  public function handle()
  {

    $tenantName = Str::of($this->argument('newTenantName'))->slug();
    $adminEmail = $this->argument('newAdminEmail');
    $adminPassword = $this->argument('newAdminPassword');

    // Create tenant
    $tenant = new Tenant;
    $tenant->id = Str::of($tenantName)->slug();
    $tenant->storage_limit_file = 1;
    $tenant->storage_limit_database = 1;
    $tenant->save();

    // Create domains
    foreach (config('tenancy.central_domains') as $centralDomain) {
      $tenant->domains()->create(['domain' => "{$tenantName}.{$centralDomain}"]);
    }

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

      $user->email_verified_at = now();
      $user->remember_token = Str::random(10);

      $user->save();
    });

    // Seed tenant data
    $tenant->run(function (Tenant $tenant) {

      $types = [
        // integer
        "tinyInteger",
        "unsignedTinyInteger",
        "smallInteger",
        "unsignedSmallInteger",
        "integer",
        "unsignedInteger",
        "mediumInteger",
        "unsignedMediumInteger",
        "bigInteger",
        "unsignedBigInteger",

        // float
        "decimal",
        "unsignedDecimal",
        "float",
        "double",

        // text
        "char",
        "string",
        "tinyText",
        "text",
        "mediumText",
        "longText",

        //other
        "boolean",
        "date",
        "time",
        "dateTime",
        "timestamp",
      ];

      foreach ($types as $index => $type) {
        $collectionFieldType = CollectionFieldType::create([
          'name' => $type,
          'datatype' => $type
        ]);
      }

      $collectionFieldTypeRichText = CollectionFieldType::create([
        'name' => "richText",
        'datatype' => "longText"
      ]);

      $tenantRequestLoggingSetting = Setting::insert([
        [
          'key' => "request_logging",
          'value' => false
        ],
        [
          'key' => "client_request_logging",
          'value' => false
        ],
      ]);
    });

    $this->info('Tenant ' . $tenantName . ' created!');
    return;
  }
}
