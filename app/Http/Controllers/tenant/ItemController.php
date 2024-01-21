<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Models\tenant\Item;
use App\Models\tenant\FieldType;

class ItemController extends Controller
{
  public function index(Request $request)
  {
    try {

      $query = Item::query();
      $query->with(['type']);

      if (!$request->requesting_user || $request->requesting_user->public) {
        $query->where('published', true);
      }

      $items = $query->get();

      return response([
        'message' => 'All items.',
        'items' => $items,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show(Request $request, String $itemName)
  {
    try {

      $query = Item::query();
      $query->with(['type']);
      $query->where('name', $itemName);

      if (!$request->requesting_user || $request->requesting_user->public) {
        $query->where('published', true);
      }

      $item = $query->first();

      return response([
        'message' => 'Single items.',
        'item' => $item,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function store(Request $request)
  {

    $request->validate([
      'name' => ['required', 'string', Rule::unique('items')],
      'value' => ['string'],
      'type_id' => ['required', 'integer'],
    ]);

    try {

      $itemType = FieldType::where('id', $request->type_id)->first();

      $formattedValue = null;

      switch ($itemType->name) {
        case "tinyInteger":
          $formattedValue = (string)$request->value;
          break;
        case "unsignedTinyInteger":
          $formattedValue = (int)$request->value;
          break;
        case "smallInteger":
          $formattedValue = (int)$request->value;
          break;
        case "unsignedSmallInteger":
          $formattedValue = (int)$request->value;
          break;
        case "integer":
          $formattedValue = (int)$request->value;
          break;
        case "unsignedInteger":
          $formattedValue = (int)$request->value;
          break;
        case "mediumInteger":
          $formattedValue = (int)$request->value;
          break;
        case "unsignedMediumInteger":
          $formattedValue = (int)$request->value;
          break;
        case "bigInteger":
          $formattedValue = (int)$request->value;
          break;
        case "unsignedBigInteger":
          $formattedValue = (int)$request->value;
          break;
        case "decimal":
          $formattedValue = (float)$request->value;
          break;
        case "unsignedDecimal":
          $formattedValue = (float)$request->value;
          break;
        case "float":
          $formattedValue = (float)$request->value;
          break;
        case "double":
          $formattedValue = (float)$request->value;
          break;
        case "char":
          $formattedValue = (string)$request->value;
          break;
        case "string":
          $formattedValue = (string)$request->value;
          break;
        case "tinyText":
          $formattedValue = (string)$request->value;
          break;
        case "text":
          $formattedValue = (string)$request->value;
          break;
        case "mediumText":
          $formattedValue = (string)$request->value;
          break;
        case "longText":
          $formattedValue = (string)$request->value;
          break;
        case "boolean":
          $formattedValue = (bool)$request->value;
          break;
        case "date":
          $carbonDate = Carbon::parse($request->value);
          $formattedValue = (string)$carbonDate->toDateString();
          break;
        case "time":
          $carbonDate = Carbon::parse($request->value);
          $formattedValue = (string)$carbonDate->toTimeString();
          break;
        case "dateTime":
          $carbonDate = Carbon::parse($request->value);
          $formattedValue = (string)$carbonDate->toDateTimeString();
          break;
        case "timestamp":
          $carbonDate = Carbon::parse($request->value);
          $formattedValue = (string)$carbonDate->getTimestamp();
          break;
      }

      $newItem = Item::create([
        'name' => Str::of($request->name)->slug('_'),
        'value' => $formattedValue,
        'type_id' => $itemType->id
      ]);

      $newItem->load(['type']);

      return response([
        'message' => 'Item created.',
        'item' => $newItem,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, Item $item)
  {

    $request->validate([
      'name' => ['string', Rule::unique('items')],
      'value' => [],
      'published' => ['boolean']
    ]);

    try {

      $item->load(['type']);

      if ($request->has('name')) {
        $item->name = Str::of($request->name)->slug('_');
      }

      if ($request->has('published')) {
        $item->published = $request->published;
      }

      if ($request->has('value')) {
        $formattedValue = null;

        switch ($item->type->name) {
          case "tinyInteger":
            $formattedValue = (string)$request->value;
            break;
          case "unsignedTinyInteger":
            $formattedValue = (int)$request->value;
            break;
          case "smallInteger":
            $formattedValue = (int)$request->value;
            break;
          case "unsignedSmallInteger":
            $formattedValue = (int)$request->value;
            break;
          case "integer":
            $formattedValue = (int)$request->value;
            break;
          case "unsignedInteger":
            $formattedValue = (int)$request->value;
            break;
          case "mediumInteger":
            $formattedValue = (int)$request->value;
            break;
          case "unsignedMediumInteger":
            $formattedValue = (int)$request->value;
            break;
          case "bigInteger":
            $formattedValue = (int)$request->value;
            break;
          case "unsignedBigInteger":
            $formattedValue = (int)$request->value;
            break;
          case "decimal":
            $formattedValue = (float)$request->value;
            break;
          case "unsignedDecimal":
            $formattedValue = (float)$request->value;
            break;
          case "float":
            $formattedValue = (float)$request->value;
            break;
          case "double":
            $formattedValue = (float)$request->value;
            break;
          case "char":
            $formattedValue = (string)$request->value;
            break;
          case "string":
            $formattedValue = (string)$request->value;
            break;
          case "tinyText":
            $formattedValue = (string)$request->value;
            break;
          case "text":
            $formattedValue = (string)$request->value;
            break;
          case "mediumText":
            $formattedValue = (string)$request->value;
            break;
          case "longText":
            $formattedValue = (string)$request->value;
            break;
          case "boolean":
            $formattedValue = (bool)$request->value;
            break;
          case "date":
            $carbonDate = Carbon::parse($request->value);
            $formattedValue = (string)$carbonDate->toDateString();
            break;
          case "time":
            $carbonDate = Carbon::parse($request->value);
            $formattedValue = (string)$carbonDate->toTimeString();
            break;
          case "dateTime":
            $carbonDate = Carbon::parse($request->value);
            $formattedValue = (string)$carbonDate->toDateTimeString();
            break;
          case "timestamp":
            $carbonDate = Carbon::parse($request->value);
            $formattedValue = (string)$carbonDate->getTimestamp();
            break;
        }

        $item->value = $formattedValue;
      }

      $item->save();

      return response([
        'message' => 'Item updated.',
        'item' => $item,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Request $request, Item $item)
  {
    try {

      $item->delete();

      return response([
        'message' => 'Item removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
