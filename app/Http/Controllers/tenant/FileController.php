<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File as FileValidation;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Database\Eloquent\Builder;

// Models
use App\Traits\RequestHelperTrait;
use App\Models\tenant\File;

class FileController extends Controller
{

  use RequestHelperTrait;

  public function index(Request $request)
  {
    try {

      $query = File::query();

      if ($request->query('name')) {
        $query->where('name', 'LIKE', "%{$request->query('name')}%");
      }

      if ($request->query('alternative_text')) {
        $query->where('alternative_text', 'LIKE', "%{$request->query('alternative_text')}%");
      }

      if ($request->query('caption')) {
        $query->where('caption', 'LIKE', "%{$request->query('caption')}%");
      }

      if ($request->query('text')) {
        $query->orWhere('name', 'LIKE', "%{$request->query('text')}%")
          ->orWhere('alternative_text', 'LIKE', "%{$request->query('text')}%")
          ->orWhere('caption', 'LIKE', "%{$request->query('text')}%");
      }

      if ($request->query('extension')) {
        $query->where('extension', $request->query('extension'));
      }

      if ($request->query('mime_type')) {
        $query->where('mime_type', $request->query('mime_type'));
      }

      if ($request->query('collection_id')) {
        $collectioId = $request->query('collection_id');
        // $query->where('collection', $request->query('collection'));
        $query->whereHas('collections', function (Builder $query) use ($collectioId) {
          $query->where('collection_id', $collectioId);
        });
      }

      if ($request->query('page') && $request->query('quantity')) {
        $query = $this->paginate($query, $request->query('page'), $request->query('quantity'));
      }

      $files = $query->latest()->with('collections')->get();

      return response([
        'message' => 'List of file records.',
        'files' => $files
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  // Upload file to system
  public function upload(Request $request)
  {

    $request->validate([
      'file' => ['required'], // 'mimetypes:jpeg,jpg,png,svg,mp4,mov,avi,pdf'
      'use_name' => ['boolean'],
    ]);

    try {

      // If no file respond with error
      if (!$request->hasFile('file')) {
        return response([
          'message' => 'No file selected for upload.',
        ], 500);
      }

      // $file = $request->hasFile('file');
      $file = $request->file('file');

      $storagePath = null;

      if ($request->use_name) {
        $existingFileRecord = File::where('name', Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->slug())->where('extension', $file->extension())->first();
        if ($existingFileRecord) {
          return response([
            'message' => 'A file with this name already exists.'
          ], 409);
        } else {
          $slugFileName = Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->slug() . "." . $file->extension();
          $storagePath = Storage::disk('public')->putFileAs(null, $file, $slugFileName);
        }
      } else {
        $storagePath = Storage::disk('public')->putFile(null, $file);
      }

      $explodeStoragePath = explode(".", $storagePath);

      [$width, $height] = getimagesize($file);
      $filename = $explodeStoragePath[0];
      $extension = $explodeStoragePath[1];

      $newFile = new File;

      $newFile->disk = "public";
      $newFile->name = $filename;
      $newFile->extension = $extension;
      $newFile->mime_type = $file->getClientMimeType();
      $newFile->path = $storagePath;
      $newFile->width = $width;
      $newFile->height = $height;
      $newFile->size = $file->getSize();

      if ($request->has('alternative_text')) {
        $newFile->alternative_text = $request->alternative_text;
      }

      if ($request->has('caption')) {
        $newFile->caption = $request->caption;
      }

      $newFile->save();

      $filePath = "{$filename}.{$extension}";
      $urlPath = "/file/{$filePath}";

      if ($request->has('collection')) {
        $newFile->collection = Str::of($request->collection)->slug();
        $urlPath = "/file/{$request->collection}/{$filePath}";
      }

      return response([
        'message' => 'File has been uploaded.',
        "file" => $newFile
      ], 200);
    } catch (QueryException $e) {
      return response([
        'message' => $e->getMessage(),
        'error' => $e
      ], 500);
    }
  }

  // Upload file to system
  public function uploadBulk(Request $request)
  {

    $request->validate([
      'use_name' => ['boolean'],
    ]);

    $files = $request->file('files');

    $uploads = [];
    $failedUploads = [];

    foreach ($files as $file) {
      try {

        $storagePath = null;

        if ($request->use_name) {
          $existingFileRecord = File::where('name', Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->slug())->where('extension', $file->extension())->first();
          if ($existingFileRecord) {
            array_push($failedUploads, [
              "original" => $file->getClientOriginalName(),
              "error" => "A file with this name already exists."
            ]);
            continue;
          } else {
            $slugFileName = Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->slug() . "." . $file->extension();
            $storagePath = Storage::disk('public')->putFileAs(null, $file, $slugFileName);
          }
        } else {
          $storagePath = Storage::disk('public')->putFile(null, $file);
        }

        $explodeStoragePath = explode(".", $storagePath);

        [$width, $height] = getimagesize($file);
        $filename = $explodeStoragePath[0];
        $extension = $explodeStoragePath[1];

        $newFile = new File;

        $newFile->disk = "public";
        $newFile->name = $filename;
        $newFile->extension = $extension;
        $newFile->mime_type = $file->getClientMimeType();
        $newFile->path = $storagePath;
        $newFile->width = $width;
        $newFile->height = $height;
        $newFile->size = $file->getSize();

        if ($request->has('alternative_text')) {
          $newFile->alternative_text = $request->alternative_text;
        }

        if ($request->has('caption')) {
          $newFile->caption = $request->caption;
        }

        $newFile->save();

        $filePath = "{$filename}.{$extension}";
        $urlPath = "/file/{$filePath}";

        if ($request->has('collection')) {
          $newFile->collection = Str::of($request->collection)->slug();
          $urlPath = "/file/{$request->collection}/{$filePath}";
        }

        array_push($uploads, $newFile);
      } catch (QueryException $e) {
        array_push($failedUploads, [
          "original" => $file->getClientOriginalName(),
          "error" => "UNKNOWN"
        ]);
      }
    }

    return response([
      'message' => 'Bulk file upload.',
      'uploaded' => $uploads,
      'failed' => $failedUploads
    ], 200);
  }

  // Update
  public function update(Request $request, File $file)
  {
    try {
      if ($request->has('name')) {
        $slugFileName = Str::of(pathinfo($request->name, PATHINFO_FILENAME))->slug();
        $slugFilePath = Str::of(pathinfo($request->name, PATHINFO_FILENAME))->slug() . "." . $file->extension;

        $existingFileRecord = File::where('path', $slugFilePath)->first();
        if (!$existingFileRecord) {
          return response([
            'message' => 'A file with this name already exists.'
          ], 409);
        }

        Storage::disk('public')->move($file->path, $slugFilePath);

        $file->name = $slugFileName;
        $file->path = $slugFilePath;
      }

      if ($request->has('alternative_text')) {
        $file->alternative_text = $request->alternative_text;
      }

      if ($request->has('caption')) {
        $file->caption = $request->caption;
      }

      if ($request->has('collection')) {
        $file->collection = Str::of($request->collection)->slug();
      }

      $file->save();

      return response([
        'message' => 'File updated.',
        'file' => $file
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

  // Delete file from system
  public function destroy(Request $request, File $file)
  {
    try {
      if ($file) {
        Storage::disk('public')->delete($file->path);
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
        'message' => $e->getMessage()
      ], 500);
    }
  }

  // Get file from system
  public function retrieveFile(Request $request, $fileName)
  {
    if (Storage::disk('public')->exists($fileName)) {
      return response()->file(storage_path("app/public/{$fileName}"));
    } else {
      return response([
        'message' => 'No matching file found.'
      ], 404);
    }
  }

  public function syncCollections(Request $request, File $file)
  {
    try {

      $file->collections()->sync($request->collection_ids);

      return response([
        'message' => 'Add file to collection.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function attachCollections(Request $request, File $file)
  {
    try {

      $file->collections()->syncWithoutDetaching($request->collection_ids);

      return response([
        'message' => 'Add file to collection.'
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function detachCollections(Request $request, File $file)
  {
    try {

      $file->collections()->detach($request->collection_ids);

      return response([
        'message' => 'Add files to collection.',
      ], 200);
    } catch (Exception $e) {
      return response([
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
