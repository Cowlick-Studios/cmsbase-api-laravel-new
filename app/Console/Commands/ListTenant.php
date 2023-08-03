<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class ListTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all tenants in the system.';

    protected function formatBytes($bytes, $precision = 2) {
      $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  
      $bytes = max($bytes, 0);
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
      $pow = min($pow, count($units) - 1);
  
      return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $tenants = Tenant::all();

      foreach($tenants as $index=>$tenant){
        $schemaName = "tenant-{$tenant->id}";
        $query = "SELECT SUM(pg_total_relation_size(pg_class.oid)) AS total_size_bytes
          FROM pg_class
          JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace
          WHERE pg_namespace.nspname = :schemaName";
        $result = DB::select($query, ['schemaName' => $schemaName]);
        $totalSizeBytes = $result[0]->total_size_bytes;

        $this->line('[' . ($index+1) . '/' . $tenants->count() . '] ' . $tenant->id . " [DB " . $this->formatBytes($totalSizeBytes) . "]" . " [File " . $this->formatBytes(0) . "]");
      }

      $this->info('Listing all tenant [' . $tenants->count() . ']');
      return;
    }
}
