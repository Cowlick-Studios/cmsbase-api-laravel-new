<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;

// Mail
use App\Mail\AuthRegisterConfirmationCode;

use App\Models\User;
use App\Models\UserRegister;
use App\Models\UserEmailChange;
use App\Models\userPasswordReset;

class UserController extends Controller
{

  private function generateVerificationCode($length = 6)
  {
    $characters = '0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];
    }
    return $randomString;
  }

  public function index(Request $request)
  {
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
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show(Request $request, User $user)
  {
    try {
      return response([
        'message' => 'User record.',
        'user' => $user
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function create(Request $request)
  {

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
      $user->password = Hash::make($request->password);
      $user->save();

      // Create email verification
      $emailVerification = new UserRegister;
      $emailVerification->email = $request->email;
      $emailVerification->verification_code = $this->generateVerificationCode();
      $emailVerification->save();

      // Send mail confirmation
      Mail::to($user)->send(new AuthRegisterConfirmationCode($user->email, $emailVerification->verification_code));

      return response([
        'message' => 'User created.',
        'user' => $user
      ], 200);
    } catch (QueryException $e) {

      if ($e->getCode() == 23505) {
        return response([
          'message' => 'A file with this name already exists.'
        ], 409);
      }

      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, User $user)
  {
    try {

      if ($request->has('name')) {
        $user->name = $request->name;
      }

      $user->save();

      return response([
        'message' => 'User updated.',
        'user' => $user
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Request $request, User $user)
  {
    try {

      UserRegister::where('email', $user->email)->delete();
      UserPasswordReset::where('email', $user->email)->delete();
      UserEmailChange::where('email', $user->email)->delete();
      UserEmailChange::where('new_email', $user->email)->delete();

      $user->delete();

      return response([
        'message' => 'User removed.',
        'user' => $user
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
