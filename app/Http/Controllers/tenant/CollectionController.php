<?php

namespace App\Http\Controllers\tenant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use DateTime;

use App\Models\tenant\Collection;
use App\Models\tenant\FieldType;
use App\Models\tenant\CollectionField;
use App\Models\tenant\User;

class CollectionController extends Controller
{

  public function index(Request $request)
  {
    try {

      $query = Collection::query();
      $query->with(['fields', 'fields.type']);

      if (!$request->requesting_user || $request->requesting_user->public) {
        $query->where('public_read', true);
      }

      $collections = $query->get();

      return response([
        'message' => 'All collections.',
        'collections' => $collections,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function feed(Request $request, $collectionName)
  {
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      if (!$collection->public_read && (!$request->requesting_user || $request->requesting_user->public)) {
        return response([
          'message' => 'Public users cannot perform read operations on this collection.',
        ], 401);
      }

      $tableName = "collection-{$collection->name}";
      $query = DB::table($tableName);
      $documents = $query->where('published', true)->latest()->get();

      $rssTitle = tenant()->id . " - " . $collection->name;
      $cmsUrl = config('app.url');

      // RSS
      $feedContent = "
<rss version='2.0'>
<channel>
    <title>{$rssTitle}</title>
    <link>{$cmsUrl}</link>
    <description>RSS Feed</description>
    <language>en-us</language>";

  foreach ($documents as $document) {
    $parsedDate = new DateTime($document->created_at);
    $rfc822Date = $parsedDate->format(DateTime::RFC822);

    $feedContent .= "
    <item>
        <title>{$document->title}</title>
        <description>{$document->description}</description>
        <link>{$document->link}</link>
        <pubDate>{$rfc822Date}</pubDate>
    </item>";
  }

  $feedContent .= "
</channel>
</rss>";
      
        return response($feedContent, 200)->header('Content-Type', 'application/rss+xml');

    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show(Request $request, $collectionName)
  {
    try {
      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      if (!$collection->public_read && (!$request->requesting_user || $request->requesting_user->public)) {
        return response([
          'message' => 'Collection not available to public users.',
        ], 401);
      }

      return response([
        'message' => 'Collections.',
        'collection' => $collection
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
      'name' => ['required', Rule::unique('collections')],
      'public_create' => ['required', 'boolean'],
      'public_read' => ['required', 'boolean'],
      'public_update' => ['required', 'boolean'],
      'public_delete' => ['required', 'boolean'],
    ]);

    try {
      $newCollection = Collection::create([
        'name' => Str::of($request->name)->slug('_'),
        'public_create' => $request->public_create,
        'public_read' => $request->public_read,
        'public_update' => $request->public_update,
        'public_delete' => $request->public_delete,
      ]);

      $tableName = "collection-{$newCollection->name}";

      Schema::create($tableName, function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->boolean('published')->default(false);
        $table->timestamps();
      });

      $newCollection->load(['fields', 'fields.type']);

      return response([
        'message' => 'New collection created.',
        'collection' => $newCollection
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, Collection $collection)
  {

    $request->validate([
      'name' => ['string', Rule::unique('collections')],
      'public_create' => ['boolean'],
      'public_read' => ['boolean'],
      'public_update' => ['boolean'],
      'public_delete' => ['boolean'],
    ]);

    try {

      $updatedCollection = $collection->update($request->all());

      return response([
        'message' => 'Collections.',
        'collection' => $updatedCollection
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Request $request, Collection $collection)
  {
    try {

      $tableName = "collection-{$collection->name}";
      Schema::dropIfExists($tableName);

      $collection->delete();

      return response([
        'message' => 'Collection removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function addField(Request $request, Collection $collection)
  {
    $request->validate([
      'name' => ['required'],
      'type_id' => ['required'],
    ]);

    try {

      $collection = $collection->load(['fields', 'fields.type']);

      $fieldType = FieldType::where('id', $request->type_id)->first();

      $newCollectionField = CollectionField::create([
        'name' => $request->name,
        'collection_id' => $collection->id,
        'type_id' => $fieldType->id
      ]);

      $newCollectionField->load(['type']);

      $tableName = "collection-{$collection->name}";
      Schema::table($tableName, function (Blueprint $table) use ($newCollectionField) {
        switch ($newCollectionField->type->datatype) {
          case "tinyInteger":
            $table->tinyInteger($newCollectionField->name)->nullable();
            break;
          case "unsignedTinyInteger":
            $table->unsignedTinyInteger($newCollectionField->name)->nullable();
            break;
          case "smallInteger":
            $table->smallInteger($newCollectionField->name)->nullable();
            break;
          case "unsignedSmallInteger":
            $table->unsignedSmallInteger($newCollectionField->name)->nullable();
            break;
          case "integer":
            $table->integer($newCollectionField->name)->nullable();
            break;
          case "unsignedInteger":
            $table->unsignedInteger($newCollectionField->name)->nullable();
            break;
          case "mediumInteger":
            $table->mediumInteger($newCollectionField->name)->nullable();
            break;
          case "unsignedMediumInteger":
            $table->unsignedMediumInteger($newCollectionField->name)->nullable();
            break;
          case "bigInteger":
            $table->bigInteger($newCollectionField->name)->nullable();
            break;
          case "unsignedBigInteger":
            $table->unsignedBigInteger($newCollectionField->name)->nullable();
            break;
          case "decimal":
            $table->decimal($newCollectionField->name)->nullable();
            break;
          case "unsignedDecimal":
            $table->unsignedDecimal($newCollectionField->name)->nullable();
            break;
          case "float":
            $table->float($newCollectionField->name)->nullable();
            break;
          case "double":
            $table->double($newCollectionField->name)->nullable();
            break;
          case "char":
            $table->char($newCollectionField->name)->nullable();
            break;
          case "string":
            $table->string($newCollectionField->name)->nullable();
            break;
          case "tinyText":
            $table->tinyText($newCollectionField->name)->nullable();
            break;
          case "text":
            $table->text($newCollectionField->name)->nullable();
            break;
          case "mediumText":
            $table->mediumText($newCollectionField->name)->nullable();
            break;
          case "longText":
            $table->longText($newCollectionField->name)->nullable();
            break;
          case "boolean":
            $table->boolean($newCollectionField->name)->nullable();
            break;
          case "date":
            $table->date($newCollectionField->name)->nullable();
            break;
          case "time":
            $table->time($newCollectionField->name)->nullable();
            break;
          case "dateTime":
            $table->dateTime($newCollectionField->name)->nullable();
            break;
          case "timestamp":
            $table->timestamp($newCollectionField->name)->nullable();
            break;
        }
      });

      return response([
        'message' => 'Collection field added.',
        'field' => $newCollectionField->load(['type'])
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function removeField(Request $request, Collection $collection, CollectionField $field)
  {
    try {

      $collection = $collection->load(['fields', 'fields.type']);
      $field->delete();

      $tableName = "collection-{$collection->name}";
      Schema::table($tableName, function (Blueprint $table) use ($field) {
        $table->dropColumn($field->name);
      });

      return response([
        'message' => 'Collection field removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  // Field types
  // TODO: Move to own controller
  public function getFieldTypes(Request $request)
  {
    try {

      $query = FieldType::query();
      $types = $query->get();

      return response([
        'message' => 'List all collection field types.',
        'types' => $types
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function createFieldType(Request $request)
  {

    $request->validate([
      'name' => ['required'],
      'datatype' => ['required'],
    ]);

    try {
      $newFieldType = FieldType::create([
        'name' => $request->name,
        'datatype' => $request->datatype,
      ]);

      return response([
        'message' => 'New collection field type created.',
        'type' => $newFieldType
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
