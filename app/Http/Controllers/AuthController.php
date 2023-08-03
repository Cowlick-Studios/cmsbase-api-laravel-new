<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

use App\Models\User;

class AuthController extends Controller
{
  public function login(Request $request){
    $request->validate([
			'email' => 'required',
			'password' => 'required'
		]);

    try {

      Log::notice('Login attempt: ' . $request->email);
      $user = User::where('email', $request->email)->first();

      if(!$user){
        return response([
          'message' => 'We could not find a matching user.'
        ], 404);
      }

      if(!Hash::check($request->password, $user->password)){
        return response([
          'message' => 'Credentials are invalid.'
        ], 404);
      }

      $jwtPrivateKey = sodium_bin2base64(sodium_crypto_sign_secretkey(sodium_base642bin(config("auth.jwt_key"), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING)), SODIUM_BASE64_VARIANT_ORIGINAL);

      $payload = [
        'iss' => config("app.url"),
        'aud' => $user->id,
        'iat' => time(),
        'exp' => time() + (3600 * 24), // 3600 is 1 hour, multiplied by 24 is a full day
        'tenant' => null
      ];

      $jwt = JWT::encode($payload, $jwtPrivateKey, 'EdDSA'); // key is required in SODIUM_BASE64_VARIANT_ORIGINAL

      return response([
        'message' => 'You have logged in successfully.',
        'access_token' => $jwt,
        'user' => $user
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
}
