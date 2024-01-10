<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

use App\Models\tenant\ClientFingerprint;
use App\Models\tenant\ClientRequestLog;

class AnalyticsController extends Controller
{

  public function index(Request $request)
  {
    try {

      $allUniqueUsers = ClientFingerprint::withCount('logs')->get();
      $allRequests = ClientRequestLog::all();

      return response([
        'message' => 'Client analytics report.',
        'unique_users' => $allUniqueUsers,
        'all_requests' => $allRequests,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function client_request(Request $request)
  {
    try {

      $existingFingerprints = ClientFingerprint::where('fingerprint', $request->fingerprint)->count();

      if ($existingFingerprints == 0) {
        ClientFingerprint::create([
          'fingerprint' => $request->fingerprint
        ]);
      }

      $requestIp = '0.0.0.0';
      if ($request->ip) {
        $requestIp = $request->ip;
      }

      ClientRequestLog::create([
        'fingerprint' => $request->fingerprint,
        'ip' => $requestIp,
        'user_agent' => $request->userAgent(),
        'url' => $request->fullUrl(),
        'country_code' => Location::get($request->ip)->countryCode
      ]);

      return response([
        'message' => 'Request successful.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
}
