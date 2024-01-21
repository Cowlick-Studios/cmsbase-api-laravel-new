<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Models\tenant\Page;
use App\Models\tenant\FieldType;
use App\Models\tenant\PageField;

class PageController extends Controller
{
  public function index(Request $request)
  {
    try {

      $query = Page::query();
      $query->with(['fields', 'fields.type']);
      if (!$request->requesting_user || $request->requesting_user->public) {
        $query->where('published', true);
      }
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
      $query->with(['fields', 'fields.type']);
      if (!$request->requesting_user || $request->requesting_user->public) {
        $query->where('published', true);
      }
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
      'name' => ['required', Rule::unique('pages')]
    ]);

    try {
      $newPage = Page::create([
        'name' => Str::of($request->name)->slug('_'),
        'data' => []
      ]);

      $newPage->load(['fields', 'fields.type']);

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
      'name' => ['string', Rule::unique('pages')],
      'data' => ['array'],
      'published' => ['boolean']
    ]);

    try {

      $page = $page->load(['fields', 'fields.type']);

      if ($request->has('name')) {
        $page->name = Str::of($request->name)->slug('_');
      }

      if ($request->has('published')) {
        $page->published = $request->published;
      }

      if ($request->has('data')) {

        $tempDataObject = $page->data;

        foreach ($page->fields as $field) {
          if (array_key_exists($field->name, $request->data)) {

            $tempDataObject[$field->name] = $request->data[$field->name];

            switch ($field->type->name) {
              case "tinyInteger":
                $tempDataObject[$field->name] = (string)$request->data[$field->name];
                break;
              case "unsignedTinyInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "smallInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "unsignedSmallInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "integer":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "unsignedInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "mediumInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "unsignedMediumInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "bigInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "unsignedBigInteger":
                $tempDataObject[$field->name] = (int)$request->data[$field->name];
                break;
              case "decimal":
                $tempDataObject[$field->name] = (float)$request->data[$field->name];
                break;
              case "unsignedDecimal":
                $tempDataObject[$field->name] = (float)$request->data[$field->name];
                break;
              case "float":
                $tempDataObject[$field->name] = (float)$request->data[$field->name];
                break;
              case "double":
                $tempDataObject[$field->name] = (float)$request->data[$field->name];
                break;
              case "char":
                $tempDataObject[$field->name] = (string)$request->data[$field->name];
                break;
              case "string":
                $tempDataObject[$field->name] = (string)$request->data[$field->name];
                break;
              case "tinyText":
                $tempDataObject[$field->name] = (string)$request->data[$field->name];
                break;
              case "text":
                $tempDataObject[$field->name] = (string)$request->data[$field->name];
                break;
              case "mediumText":
                $tempDataObject[$field->name] = (string)$request->data[$field->name];
                break;
              case "longText":
                $tempDataObject[$field->name] = (string)$request->data[$field->name];
                break;
              case "boolean":
                $tempDataObject[$field->name] = (bool)$request->data[$field->name];
                break;
              case "date":
                $carbonDate = Carbon::parse($request->data[$field->name]);
                $tempDataObject[$field->name] = (string)$carbonDate->toDateString();
                break;
              case "time":
                $carbonDate = Carbon::parse($request->data[$field->name]);
                $tempDataObject[$field->name] = (string)$carbonDate->toTimeString();
                break;
              case "dateTime":
                $carbonDate = Carbon::parse($request->data[$field->name]);
                $tempDataObject[$field->name] = (string)$carbonDate->toDateTimeString();
                break;
              case "timestamp":
                $carbonDate = Carbon::parse($request->data[$field->name]);
                $tempDataObject[$field->name] = (string)$carbonDate->getTimestamp();
                break;
            }
          }
        }

        $page->data = $tempDataObject;
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

      $existingField = PageField::where('page_id', $page->id)->where('name', $request->name)->first();

      if ($existingField) {
        return response([
          'message' => 'Page field already exists.',
        ], 409);
      }

      $page = $page->load(['fields', 'fields.type']);

      $pageFieldType = FieldType::where('id', $request->type_id)->first();

      $newPageField = PageField::create([
        'name' => $request->name,
        'page_id' => $page->id,
        'type_id' => $pageFieldType->id
      ]);

      $newPageField->load(['type']);

      $tempPageData = $page->data;
      $tempPageData[$request->name] = null;
      $page->data = $tempPageData;
      $page->save();

      return response([
        'message' => 'Page field added.',
        'field' => $newPageField->load(['type'])
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  public function removeField(Request $request, Page $page, PageField $field)
  {
    try {

      $page = $page->load(['fields', 'fields.type']);
      $field->delete();

      $tempPageData = $page->data;
      unset($tempPageData[$field->name]);
      $page->data = $tempPageData;
      $page->save();

      return response([
        'message' => 'Page field removed.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage(),
      ], 500);
    }
  }
}
