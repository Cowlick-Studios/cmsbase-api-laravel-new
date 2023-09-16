<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File as FileValidation;
use Illuminate\Database\QueryException;

// Models
use App\Traits\RequestHelperTrait;
use App\Models\tenant\File;

class FileController extends Controller
{

  use RequestHelperTrait;

  public function index(Request $request){
    try {
      
      $query = File::query();

      if($request->query('name')){
        $query->where('name', 'LIKE', "%{$request->query('name')}%");
      }

      if($request->query('alternative_text')){
        $query->where('alternative_text', 'LIKE', "%{$request->query('alternative_text')}%");
      }

      if($request->query('caption')){
        $query->where('caption', 'LIKE', "%{$request->query('caption')}%");
      }

      if($request->query('extension')){
        $query->where('extension', $request->query('extension'));
      }

      if($request->query('mime_type')){
        $query->where('mime_type', $request->query('mime_type'));
      }

      if($request->query('collection')){
        $query->where('collection', Str::of($request->query('collection')->slug()));
      }

      if($request->query('page') && $request->query('quantity')){
        $query = $this->paginate($query, $request->query('page'), $request->query('quantity'));
      }

      $files = $query->latest()->get();

      return response([
        'message' => 'List of file records.',
        'files' => $files
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  // Upload file to system
  public function upload(Request $request){

    $request->validate([
			'file' => ['required'], // 'mimetypes:jpeg,jpg,png,svg,mp4,mov,avi,pdf'
		]);

    try {

      // If no file respond with error
      if (!$request->hasFile('file')) {
        return response([
          'message' => 'No file selected for upload.',
        ], 500);
      }

      //$storagePath = $request->file('file')->store();
      $storagePath = Storage::disk('local')->putFile(null, $request->file('file'));
      [$width, $height] = getimagesize($request->file('file'));
      $filename = pathinfo($storagePath, PATHINFO_FILENAME);
      $extension = pathinfo($storagePath, PATHINFO_EXTENSION);

      $newFile = new File;

      if($request->has('use_name') && boolval($request->use_name)){
        $filename = Str::of(pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME))->slug();
        $extension = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_EXTENSION);
      }

      $newFile->name = $filename;
      $newFile->extension = $extension;
      $newFile->mime_type = $request->file('file')->getClientMimeType();
      $newFile->path = $storagePath;
      $newFile->width = $width;
      $newFile->height = $height;
      $newFile->size = $request->file('file')->getSize();

      if($request->has('collection')){
        $newFile->collection = Str::of($request->collection)->slug();
      }

      if($request->has('alternative_text')){
        $newFile->alternative_text = $request->alternative_text;
      }

      if($request->has('caption')){
        $newFile->caption = $request->caption;
      }

      $newFile->save();

      $filePath = "{$filename}.{$extension}";
      $urlPath = "/file/{$filePath}";

      if($request->has('collection')){
        $urlPath = "/file/{$request->collection}/{$filePath}";
      }

      return response([
        'message' => 'File has been uploaded.',
        'file' => $filePath,
        'uri' => $urlPath
      ], 200);
      
    } catch (QueryException $e) {

      Storage::disk('local')->delete($storagePath);

      if($e->getCode() == 23505){
        return response([
          'message' => 'A file with this name already exists.'
        ], 409);
      }

      return response([
        'message' => 'Server error.',
        'error' => $e
      ], 500);
    }
  }

  // Update
  public function update(Request $request, $fileName){
    try {
      [$name, $extension] = explode(".", $fileName);

      $file = File::where('name', $name)->where('extension', $extension)->first();

      if($file){

        if($request->has('name')){
          $file->name = $request->name;
        }
  
        if($request->has('alternative_text')){
          $file->alternative_text = $request->alternative_text;
        }
  
        if($request->has('caption')){
          $file->caption = $request->caption;
        }
  
        if($request->has('collection')){
          $file->collection = Str::of($request->collection)->slug();
        }

        $file->save();

        return response([
          'message' => 'File updated.',
          'file' => $file
        ], 200);

      } else {
        return response([
          'message' => 'No matching file found.'
        ], 404);
      }
    } catch (QueryException $e) {

      if($e->getCode() == 23505){
        return response([
          'message' => 'A file with this name already exists.'
        ], 409);
      }

      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  // Get file from system
  public function retrieveFile(Request $request, $fileName){
    try {
      [$name, $extension] = explode(".", $fileName);
      $file = File::where('name', $name)->where('extension', $extension)->where('collection', null)->first();
      if($file){
        return response()->file(storage_path('app/' . $file->path));
      } else {
        return response([
          'message' => 'No matching file found.'
        ], 404);
      }
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

  // Get file from system by category
  public function retrieveFileByCollection(Request $request, $collection, $fileName){
    try {
      [$name, $extension] = explode(".", $fileName);
      $file = File::where('name', $name)->where('extension', $extension)->where('collection', Str::of($collection)->slug())->first();
      if($file){
        return response()->file(storage_path('app/' . $file->path));
      } else {
        return response([
          'message' => 'No matching file found.'
        ], 404);
      }
    } catch (Exception $e) {
      return response([
        'message' => 'Server error.'
      ], 500);
    }
  }

    // Delete file from system
    public function destroyFile(Request $request, $fileName){
      try {
        [$name, $extension] = explode(".", $fileName);

        $file = File::where('name', $name)->where('extension', $extension)->where('collection', null)->first();

        if($file){
          Storage::disk('local')->delete($file->path);
          $file->delete();

          return response([
            'message' => 'File has been removed.'
          ], 200);
        } else {
          return response([
            'message' => 'No matching file found.'
          ], 404);
        }
      } catch (Exception $e) {
        return response([
          'message' => 'Server error.'
        ], 500);
      }
    }
  
    // Delete file from system by category
    public function destroyFileByCollection(Request $request, $collection, $fileName){
      try {
        [$name, $extension] = explode(".", $fileName);

        $file = File::where('name', $name)->where('extension', $extension)->where('collection', Str::of($collection)->slug())->first();

        if($file){
          Storage::disk('local')->delete($file->path);
          $file->delete();

          return response([
            'message' => 'File has been removed.'
          ], 200);
        } else {
          return response([
            'message' => 'No matching file found.'
          ], 404);
        }
      } catch (Exception $e) {
        return response([
          'message' => 'Server error.'
        ], 500);
      }
    }
}
