<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;

use App\Models\tenant\Page;
use App\Models\tenant\CollectionFieldType;

class PageController extends Controller
{
  public function index(Request $request)
  {
    try {

      $query = Page::query();
      $pages = $query->get();

      return response([
        'message' => 'All pages.',
        'pages' => $pages,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show(Request $request, $pageName)
  {
    try {
      $query = Page::query();
      $page = $query->where('name', $pageName)->first();

      return response([
        'message' => 'Page.',
        'page' => $page
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
      'name' => ['required']
    ]);

    try {
      $newPage = Page::create([
        'name' => $request->name,
        'schema' => [],
        'data' => []
      ]);

      return response([
        'message' => 'New page created.',
        'page' => $newPage
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  public function update(Request $request, Page $page)
  {

    $request->validate([
      'name' => ['string'],
    ]);

    try {

      if ($request->has('name')) {
        $page->name = $request->name;
      }

      if ($request->has('data')) {

        $data = $page->data;

        foreach ($page->schema as $fieldName => $fieldInfo) {
          if (array_key_exists($fieldName, $request->data)) {
            switch ($fieldInfo["type"]) {
              case "tinyInteger":
                $data[$fieldName] = (string)$request->data[$fieldName];
                break;
              case "unsignedTinyInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "smallInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "unsignedSmallInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "integer":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "unsignedInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "mediumInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "unsignedMediumInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "bigInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "unsignedBigInteger":
                $data[$fieldName] = (int)$request->data[$fieldName];
                break;
              case "decimal":
                $data[$fieldName] = (float)$request->data[$fieldName];
                break;
              case "unsignedDecimal":
                $data[$fieldName] = (float)$request->data[$fieldName];
                break;
              case "float":
                $data[$fieldName] = (float)$request->data[$fieldName];
                break;
              case "double":
                $data[$fieldName] = (float)$request->data[$fieldName];
                break;
              case "char":
                $data[$fieldName] = (string)$request->data[$fieldName];
                break;
              case "string":
                $data[$fieldName] = (string)$request->data[$fieldName];
                break;
              case "tinyText":
                $data[$fieldName] = (string)$request->data[$fieldName];
                break;
              case "text":
                $data[$fieldName] = (string)$request->data[$fieldName];
                break;
              case "mediumText":
                $data[$fieldName] = (string)$request->data[$fieldName];
                break;
              case "longText":
                $data[$fieldName] = (string)$request->data[$fieldName];
                break;
              case "boolean":
                $data[$fieldName] = (bool)$request->data[$fieldName];
                break;
              case "date":
                $carbonDate = Carbon::parse($request->data[$fieldName]);
                $data[$fieldName] = (string)$carbonDate->toDateString();
                break;
              case "time":
                $carbonDate = Carbon::parse($request->data[$fieldName]);
                $data[$fieldName] = (string)$carbonDate->toTimeString();
                break;
              case "dateTime":
                $carbonDate = Carbon::parse($request->data[$fieldName]);
                $data[$fieldName] = (string)$carbonDate->toDateTimeString();
                break;
              case "timestamp":
                $carbonDate = Carbon::parse($request->data[$fieldName]);
                $data[$fieldName] = (string)$carbonDate->getTimestamp();
                break;
            }
          }
        }

        $page->data = $data;
      }

      $page->save();

      return response([
        'message' => 'Page updated.',
        'page' => $page
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  public function destroy(Request $request, Page $page)
  {
    try {

      $page->delete();

      return response([
        'message' => 'Page removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function addField(Request $request, Page $page)
  {
    $request->validate([
      'name' => ['required'],
      'type_id' => ['required'],
    ]);

    try {

      $collectionFieldType = CollectionFieldType::where('id', $request->type_id)->first();

      if (array_key_exists($request->name, $page->schema) && array_key_exists($request->name, $page->data)) {
        throw new Exception('Field already exists on schema or data.');
      }

      $schema = $page->schema;
      $schema[$request->name] = [
        'type' => $collectionFieldType->name,
        'type_id' => $collectionFieldType->id
      ];
      $page->schema = $schema;

      $data = $page->data;
      $data[$request->name] = null;
      $page->data = $data;

      $page->save();

      return response([
        'message' => 'Page field added.',
        'page' => $page
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  public function removeField(Request $request, Page $page)
  {
    try {

      if (!array_key_exists($request->field, $page->schema) && !array_key_exists($request->field, $page->data)) {
        throw new Exception('Field does not exist on schema or data.');
      }

      $schema = $page->schema;
      unset($schema[$request->field]);
      $page->schema = $schema;

      $data = $page->data;
      unset($data[$request->field]);
      $page->data = $data;

      $page->save();

      return response([
        'message' => 'Page field removed.',
        'page' => $page
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage(),
      ], 500);
    }
  }
}
