<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

use App\Models\tenant\RequestLog;
use App\Models\tenant\Setting;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $response = $next($request);

        $logRequestSetting = Setting::where('key', 'request_logging')->first();

        if($logRequestSetting->value == 'true'){
          $requestLogData = [
            'user_id' => $request->requesting_user?->id,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status' => 200
          ];
  
          if (method_exists($request, 'status')) {
            $requestLogData['status'] = $response->status();
          }
  
          RequestLog::create($requestLogData);
        }

        return $response;
    }
}
