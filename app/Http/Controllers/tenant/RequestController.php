<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\tenant\RequestLog;

class RequestController extends Controller
{
  public function index(Request $request)
  {
    try {

      $requests = RequestLog::with(['user'])->orderBy('created_at', 'desc')->get();
      $pastDay = RequestLog::where('created_at', '>=', now()->subHours(24))->orderBy('created_at', 'desc')->get();

      return response([
        'message' => 'List of system requests.',
        'pastDay' => $pastDay,
        'requests' => $requests,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function clear(Request $request)
  {
    try {

      RequestLog::truncate();

      return response([
        'message' => 'All requests cleared.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
