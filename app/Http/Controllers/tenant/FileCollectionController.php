<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\RequestHelperTrait;
use App\Models\tenant\File;
use App\Models\tenant\FileCollection;

class FileCollectionController extends Controller
{

  use RequestHelperTrait;

  public function index(Request $request){
    try {

      $collections = FileCollection::all();

      return response([
        'message' => 'List of file collections.',
        'collections' => $collections
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function show(Request $request, Collection $collection){
    try {

      $collection = $collection->load(['files']);

      return response([
        'message' => 'List files in collection.',
        'collection' => $collection
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function create(Request $request){
    try {

      $newCollection = FileCollection::create([
        'name' => $request->name
      ]);

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

  public function update(Request $request, Collection $collection){
    try {
      return response([
        'message' => 'Collection updated.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
  
  public function destroy(Request $request, Collection $collection){
    try {
      return response([
        'message' => 'Collection removed.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function addFiles(Request $request, Collection $collection){
    try {

      $collection->files()->syncWithoutDetaching($request["files"]);
      
      return response([
        'message' => 'Add file to collection.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  public function removeFile(Request $request, Collection $collection, File $file){
    try {

      $collection->files()->detach($file->id);

      return response([
        'message' => 'Add files to collection.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }
}
