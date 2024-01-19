<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\tenant\Setting;

class SettingsController extends Controller
{
  public function index(Request $request)
  {
    try {

      $settings = Setting::all();

      return response([
        'message' => 'List of all system settings.',
        'settings' => $settings
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, $requestKey)
  {

    $request->validate([
      'value' => ['required'],
    ]);

    try {

      $setting = Setting::where('key', $requestKey)->first();

      $setting->value = $request->value;

      $setting->save();

      return response([
        'message' => 'Updated system setting.',
        'setting' => $setting
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
