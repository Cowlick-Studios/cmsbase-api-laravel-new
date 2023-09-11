<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Carbon\Carbon;

use App\Models\User;
use App\Models\EmailVerification;

class UserController extends Controller
{

  private function getRandomString($length = 20){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];
    }
    return $randomString;
  }

  public function index(Request $request){
    try {

      $query = User::query();
      $query->orderBy('created_at', 'desc');
      $users = $query->get();

      return response([
        'message' => 'List all users.',
        'users' => $users
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function show(Request $request, User $user){
    try {
      return response([
        'message' => 'User record.',
        'user' => $user
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function create(Request $request){

    $request->validate([
			'name' => ['required'],
      'email' => ['required'],
      'password' => ['required'],
		]);

    try {

      // Create tenant
      $user = new User;
      $user->name = $request->name;
      $user->email = $request->email;
      $user->password = $request->password;
      $user->save();

      // Create email verification
      $emailVerification = new EmailVerification;
      $emailVerification->user_id = $user->id;
      $emailVerification->code = $this->getRandomString(rand(20, 30));
      $emailVerification->save();

      // Send mail confirmation
      Mail::to($user)->send(new VerifyEmail('', $emailVerification->code));

      return response([
        'message' => 'User created.',
        'user' => $user
      ], 200);
    } catch (QueryException $e) {

      if($e->getCode() == 23505){
        return response([
          'message' => 'A file with this name already exists.'
        ], 409);
      }

      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function update(Request $request, User $user){
    try {

      if($request->has('name')){
        $user->name = $request->name;
      }

      $user->save();

      return response([
        'message' => 'User updated.',
        'user' => $user
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function destroy(Request $request, User $user){
    try {

      $user->delete();

      return response([
        'message' => 'User removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function verifyEmail(Request $request, User $user){

    $request->validate([
			'code' => ['required'],
		]);

    try {

      $emailVerification = EmailVerification::where('user_id', $user->id)->first();

      if($request->code !== $emailVerification->code){

        $emailVerification->code = $this->getRandomString(rand(20, 30));
        $emailVerification->save();

        Mail::to($user)->send(new VerifyEmail('', $emailVerification->code));

        return response([
          'message' => 'Incorrect verification code.'
        ], 401);
      }

      $now = new Carbon();
      $user->email_verified_at = $now;
      $user->save();

      return response([
        'message' => 'Email verified.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
}
