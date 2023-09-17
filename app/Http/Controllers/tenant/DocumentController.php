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

class DocumentController extends Controller
{

  public function index (Request $request, $collectionName){
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      if(!$collection->public_read && (!$request->requesting_user || $request->requesting_user->public)){
        return response([
          'message' => 'Collection not available to public users.',
        ], 401);
      }

      $tableName = "collection-{$collection->name}";
      $query = DB::table($tableName);
      $documents = $query->orderBy('updated_at', 'desc')->get();

      return response([
        'message' => 'All collections documents.',
        'documents' => $documents
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function show (Request $request, $collectionName, $documentId){
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      if(!$collection->public_read && (!$request->requesting_user || $request->requesting_user->public)){
        return response([
          'message' => 'Collection not available to public users.',
        ], 401);
      }

      $tableName = "collection-{$collection->name}";
      $query = DB::table($tableName)->where('id', $documentId);
      $document = $query->orderBy('updated_at', 'desc')->first();

      return response([
        'message' => 'Collection document.',
        'document' => $document
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function store (Request $request, $collectionName){
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      $record = $request->all();

      $record["user_id"] = $request->requesting_user->id;
      $record["published"] = false;
      $record["created_at"] = now();
      $record["updated_at"] = now();

      unset($record['requesting_user']);

      $tableName = "collection-{$collection->name}";
      $newDocumentId = DB::table($tableName)->insertGetId($record);
      $newDocument = DB::table($tableName)->where('id', $newDocumentId)->first();

      return response([
        'message' => 'New document created.',
        'document' => $newDocument,
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function update (Request $request, $collectionName, $documentId){
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      $updatedDocument = $request->all();
      unset($updatedDocument['requesting_user']);

      $tableName = "collection-{$collection->name}";
      DB::table($tableName)->where('id', $documentId)->update($updatedDocument);
      $updated = DB::table($tableName)->where('id', $documentId)->first();

      return response([
        'message' => 'Document removed.',
        'document' => $updated
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function destroy (Request $request, $collectionName, $documentId){
    try {

      $collection = Collection::with(['fields', 'fields.type'])->where('name', $collectionName)->first();

      $tableName = "collection-{$collection->name}";
      $deleted = DB::table($tableName)->where('id', $documentId)->delete();

      return response([
        'message' => 'Document removed.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
}
