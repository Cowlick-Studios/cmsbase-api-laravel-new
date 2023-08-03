<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';

		protected $fillable = [
      'disk',
      'name',
      'extension',
      'mime_type',
      'path',
      'collection',
      'width',
      'height',
      'size',
      'alternative_text',
      'caption',
    ];

    protected $hidden = ['path'];

    protected $appends = ['uri', 'file'];

    public function getFileAttribute()
    {
      $filePath = "{$this->name}.{$this->extension}";
      return $filePath;
    }

    public function getUriAttribute()
    {
      $filePath = "{$this->name}.{$this->extension}";

      $urlPath = "/file/{$filePath}";

      if($this->collection){
        $urlPath = "/file/{$this->collection}/{$filePath}";
      }
      
      return $urlPath;
    }
}
