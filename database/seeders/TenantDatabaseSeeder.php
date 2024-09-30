<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\tenant\ClientFingerprint;
use App\Models\tenant\ClientAnalytic;
use App\Models\tenant\ClientAnalyticCountry;

class TenantDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

    \App\Models\tenant\User::factory()->create([
      'name' => 'user',
      'email' => 'user@example.com',
      'admin' => true,
      'public' => false,
    ]);

    \App\Models\tenant\User::factory(10)->create();

    // Fingerprinting & analytics
    // ==========================
    
    // ClientFingerprint::factory(100)->create();

    // for ($i = 0; $i < 365; $i++) {
    //   ClientAnalytic::factory()->create([
    //     'date' => Carbon::now()->subDays($i),
    //     'request_count' => rand(100, 200),
    //   ]);
    // }

    // $allAnalytics = ClientAnalytic::all();

    // foreach ($allAnalytics as $analytic) {
    //   $randomFingerprints = ClientFingerprint::inRandomOrder()->take(rand(10, 50))->get();

    //   $syncFingerprints = [];

    //   foreach ($randomFingerprints as $randomFingerprint) {
    //     $syncFingerprints[$randomFingerprint->id] = [
    //       'request_count' => floor($analytic->request_count / sizeof($randomFingerprints))
    //     ];
    //   }

    //   // Attach to request
    //   $analytic->fingerprints()->syncWithoutDetaching($syncFingerprints);

    //   foreach ($randomFingerprints as $fingerprint) {

    //     $existingCountry = ClientAnalyticCountry::where('client_analytic_id', $analytic->id)->where('country_code', $fingerprint->country_code)->first();

    //     if ($existingCountry) {
    //       $existingCountry->request_count = $existingCountry->request_count + floor($analytic->request_count / sizeof($randomFingerprints));
    //     } else {
    //       ClientAnalyticCountry::factory()->create([
    //         'client_analytic_id' => $analytic->id,
    //         'country_code' => $fingerprint->country_code,
    //         'request_count' => floor($analytic->request_count / sizeof($randomFingerprints))
    //       ]);
    //     }
    //   }
    // }
  }
}
