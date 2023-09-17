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

use App\Models\tenant\Collection;
use App\Models\tenant\CollectionFieldType;
use App\Models\tenant\CollectionField;
use App\Models\tenant\User;

class CollectionController extends Controller
{

  public function index (Request $request){
    try {

      $query = Collection::query();
      $query->with(['fields', 'fields.type']);

      if(!$request->requesting_user || $request->requesting_user->public){
        $query->where('public_read', true);
      }
      
      $collections = $query->get();

      return response([
        'message' => 'All collections.',
        'collections' => $collections,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function show (Request $request, $collectionName){
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      if(!$collection->public_read && (!$request->requesting_user || $request->requesting_user->public)){
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
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function store (Request $request){
    $request->validate([
			'name' => ['required'],
      'public_create' => ['required'],
      'public_read' => ['required'],
      'public_update' => ['required'],
      'public_delete' => ['required'],
		]);

    try {
      $newCollection = Collection::create([
        'name' => $request->name,
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

      return response([
        'message' => 'New collection created.',
        'collection' => $newCollection
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function addField (Request $request, $collectionName){
    $request->validate([
			'name' => ['required'],
      'type' => ['required'],
		]);

    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      $collectionFieldType = CollectionFieldType::where('name', $request->type)->first();

      $newCollectionField = CollectionField::create([
        'name' => $request->name,
        'collection_id' => $collection->id,
        'type_id' => $collectionFieldType->id
      ]);

      $newCollectionField->load(['type']);

      $tableName = "collection-{$collection->name}";
      Schema::table($tableName, function (Blueprint $table) use ($newCollectionField) {
        switch ($newCollectionField->type->datatype){
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
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function removeField (Request $request, $collectionName, $fieldName){
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();
      $collectionFieldTypeRemoved = CollectionField::where('collection_id', $collection->id)->where('name', $fieldName)->delete();

      $tableName = "collection-{$collection->name}";
      Schema::table($tableName, function (Blueprint $table) use ($fieldName) {
        $table->dropColumn($fieldName);
      });

      return response([
        'message' => 'Collection field removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function destroy (Request $request, Collection $collection){
    try {

      $tableName = "collection-{$collection->name}";
      Schema::dropIfExists($tableName);

      $collection->delete();

      return response([
        'message' => 'Collection removed.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }




  // Field types
  // TODO: Move to own controller
  public function getFieldTypes(Request $request){
    try {

      $query = CollectionFieldType::query();
      $types = $query->get();

      return response([
        'message' => 'List all collection field types.',
        'types' => $types
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function createFieldType(Request $request){

    $request->validate([
			'name' => ['required'],
      'datatype' => ['required'],
		]);

    try {
      $newCollectionFieldType = CollectionFieldType::create([
        'name' => $request->name,
        'datatype' => $request->datatype,
      ]);

      return response([
        'message' => 'New collection field type created.',
        'type' => $newCollectionFieldType
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
}
