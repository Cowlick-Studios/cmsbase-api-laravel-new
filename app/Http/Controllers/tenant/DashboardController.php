<?php

namespace App\Http\Controllers\tenant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class DashboardController extends Controller
{

  private function bytesToGB($bytes) {
    $bytes / 1000 / 1000 / 1000;
  }

  private function getTotalSizeOfFilesInDirectory($directoryPath){
    $finder = new Finder();
    $finder->files()->in($directoryPath);

    $totalSize = 0;

    foreach ($finder as $file) {
        $totalSize += File::size($file->getRealPath());
    }

    return $totalSize;
  }

  public function index(Request $request){
    try {
      $schemaName = "tenant-" . tenant()->id;

      $query = DB::select("
        SELECT SUM(pg_total_relation_size(quote_ident(tablename)::text)) AS schema_size
        FROM pg_tables
        WHERE schemaname = '{$schemaName}'
      "); // bytes

      return response([
        'message' => 'Dashboard.',
        'tenant' => tenant(),
        'database_usage' => (int) $query[0]->schema_size,
        'file_usage' => $this->getTotalSizeOfFilesInDirectory(storage_path())
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
}
