<?php

namespace App\Http\Controllers\tenant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;

use App\Models\tenant\User;
use App\Models\tenant\UserRegister;

use App\Mail\AuthRegisterConfirmationCode;

class UserController extends Controller
{

  private function getRandomString($length = 20)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];
    }
    return $randomString;
  }

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

      if ($request->has('public')) {
        $query->where('public', $request->query('public'));
      }

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
      'email' => ['required', Rule::unique('users')],
      'password' => ['required'],
      'public' => ['required', 'boolean']
    ]);

    try {

      // Create user
      $user = new User;
      $user->name = $request->name;
      $user->email = $request->email;
      $user->password = Hash::make($request->password);
      $user->public = $request->public;
      $user->blocked = false;
      $user->save();

      // Create email verification
      $emailVerification = new UserRegister;
      $emailVerification->email = $request->email;
      $emailVerification->verification_code = $this->generateVerificationCode();
      $emailVerification->save();

      // Send mail confirmation
      Mail::to($user)->send(new AuthRegisterConfirmationCode($user->email, $emailVerification->code, tenant()->id));

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

    $request->validate([
      'public' => ['boolean'],
      'blocked' => ['boolean'],
    ]);

    try {

      if ($request->has('name')) {
        $user->name = $request->name;
      }

      if ($request->has('public')) {
        $user->public = $request->public;
      }

      if ($request->has('blocked')) {
        $user->blocked = $request->blocked;
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

      $user->delete();

      return response([
        'message' => 'User removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
