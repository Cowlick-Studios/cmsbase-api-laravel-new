<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\tenant\ClientFingerprint;
use App\Models\tenant\ClientAnalytic;
use App\Models\tenant\ClientAnalyticCountry;
use App\Models\tenant\Setting;

class AnalyticsController extends Controller
{

  public function index(Request $request)
  {
    try {

      $clientAnalyticQuery = ClientAnalytic::with(['fingerprints', 'countryAnalytics']);

      if ($request->query('start') && $request->query('end')) {
        $clientAnalyticQuery->where('date', '>=', $request->query('start'));
        $clientAnalyticQuery->where('date', '<=', $request->query('end'));
      }

      $clientAnalytic = $clientAnalyticQuery->orderBy('date', 'asc')->get();

      return response([
        'message' => 'Client analytics report.',
        'client_analytic' => $clientAnalytic,
        'test1' => $request->query('start'),
        'test2' => $request->query('end'),
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function client_request(Request $request)
  {
    try {

      $logClientRequestSetting = Setting::where('key', 'client_request_logging')->first();
      if (!$logClientRequestSetting) {
        return response([
          'message' => 'Request successful. Not Logged.'
        ], 200);
      }

      // Create fingerprint if not exists
      $requestIp = '0.0.0.0';
      if ($request->ip) {
        $requestIp = $request->ip;
      }

      $clientFingerprintRecord = ClientFingerprint::firstOrCreate([
        'fingerprint' => $request->fingerprint,
      ], [
        'ip' => $requestIp,
        'user_agent' => $request->userAgent(),
        'country_code' => Location::get($request->ip)->countryCode,
        'request_count' => 0
      ]);
      $clientFingerprintRecord->save();

      $clientFingerprintRecord->increment('request_count');

      // Alter analytic
      $clientAnalyticRecord = ClientAnalytic::firstOrCreate([
        'date' => Carbon::now(),
      ], [
        'request_count' => 0
      ]);
      $clientAnalyticRecord->save();

      $clientAnalyticRecord->increment('request_count');

      $clientAnalyticRecord->fingerprints()->syncWithoutDetaching($clientFingerprintRecord);
      $clientAnalyticRecord->fingerprints()->where('fingerprint_id', $clientFingerprintRecord->id)->increment('request_count');

      // Alter analytic country
      $clientAnalyticCountryRecord = ClientAnalyticCountry::firstOrCreate([
        'client_analytic_id' => $clientAnalyticRecord->id,
        'country_code' => $clientFingerprintRecord->country_code,
      ], [
        'request_count' => 0
      ]);
      $clientAnalyticCountryRecord->save();

      $clientAnalyticCountryRecord->increment('request_count');

      return response([
        'message' => 'Request successful.',
        'test' => $clientAnalyticRecord->id
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
