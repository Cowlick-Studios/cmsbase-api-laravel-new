<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use  App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RemoveTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete {tenantName?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes and existing tenant from the system.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      if($this->option('all')){
        $tenants = Tenant::all();

        foreach($tenants as $tenant){
          $this->removeTenant($tenant);
        }

        return;
      }

      $tenantName = $this->argument('tenantName');

      $tenant = Tenant::where('id', $tenantName)->first();

      // Check if tenant name is already used
      if(!$tenant){
        $this->error('Tenant does not exist.');
        return;
      }

      $this->removeTenant($tenant);
      return;
    }

    protected function removeTenant(Tenant $tenant){
      // Delete tenant record and DB
      $tenant->delete();

      // Remove storage directory
      $this->rrmdir(base_path() . "/storage/tenant-{$tenant->id}");
      $this->rrmdir(base_path() . "/storage/app/public/tenant/{$tenant->id}");

      $this->info('Tenant ' . $tenant->id . ' deleted!');
    }

    protected function rrmdir($dir) { 
      if (is_dir($dir)) { 
        $objects = scandir($dir);
        foreach ($objects as $object) { 
          if ($object != "." && $object != "..") { 
            if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
              $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
            else
              unlink($dir. DIRECTORY_SEPARATOR .$object); 
          } 
        }
        rmdir($dir); 
      } 
    }
}
