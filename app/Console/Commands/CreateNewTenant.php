<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class CreateNewTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {tenantName} {adminEmail} {adminPassword}';

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

      $tenantName = $this->argument('tenantName');
      $adminEmail = $this->argument('adminEmail');
      $adminPassword = $this->argument('adminPassword');

      $checkTenant = Tenant::where('id', $tenantName)->first();

      // Check if tenant name is already used
      if($checkTenant){
        $this->error('Tenant name is already used.');
        return;
      }

      // Check if name matches pattern
      if (preg_match('/[^a-z]/', $tenantName)){
        $this->error('Tenant name can only contain lowercase letters!');
        return;
      }

      // Create tenant
      $tenant = Tenant::create(['id' => $tenantName]);

      // Create tenant domains
      foreach(config('tenancy.central_domains') as $centralDomain){
        $tenant->domains()->create(['domain' => "{$tenantName}.{$centralDomain}"]);
      }

      $tenant->run(function (Tenant $tenant) use ($tenantName, $adminEmail, $adminPassword) {
        $user = User::create([
          'name' => 'Admin',
          'email' => $adminEmail,
          'password' => bcrypt($adminPassword),
        ]);

        $user->email_verified_at = now();
        $user->remember_token = Str::random(10);

        $user->save();
      });
      
      $this->info('Tenant ' . $tenantName . ' created!');
      return;
    }
}
