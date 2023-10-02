<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\tenant\User;

class AuthenticateTokenTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

      if(!$request->hasHeader('Authorization') || $request->bearerToken() == ""){
        return response([
          'message' => 'No Authorization header found.'
        ], 401);
      }

      try {
        $jwt = $request->bearerToken();
        $jwtPublicKey = sodium_bin2base64(sodium_crypto_sign_publickey(sodium_base642bin(config("auth.jwt_key"), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING)), SODIUM_BASE64_VARIANT_ORIGINAL);
        
        $decoded = JWT::decode($jwt, new Key($jwtPublicKey, 'EdDSA')); // key is required in SODIUM_BASE64_VARIANT_ORIGINAL

        $decodedArray = json_decode(json_encode($decoded), true);

        // Check if expired
        if(time() > $decodedArray['exp']){
          return response([
            'message' => 'This token has expired.'
          ], 401);
        }

        $currentTenant = tenant();
        if($currentTenant){
          if($currentTenant->id !== $decodedArray['tenant']){
            return response([
              'message' => 'This token is not valid for this tenant.'
            ], 401);
          }
        }

        if($decodedArray['tenant'] == null){
          return response([
            'message' => 'This token is not valid for this tenant.'
          ], 401);
        }

        $user = User::where('id', $decodedArray['aud'])->first();

        if(!$user){
          return response([
            'message' => 'No matching user.',
          ], 404);
        }

        if($user->blocked){
          return response([
            'message' => 'You have been blocked.',
          ], 401);
        }

        $request['requesting_user'] = $user;

        return $next($request);

      } catch (Exception $e) {
        return response([
          'message' => 'Server Error.',
        ], 500);
      }
    }
}
