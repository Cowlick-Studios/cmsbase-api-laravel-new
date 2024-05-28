<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
      $tenantId = tenant()->id;

      $urlPath = "/storage/tenant/{$tenantId}/{$filePath}";
      
      return $urlPath;
    }

    public function collections(): BelongsToMany {
      return $this->belongsToMany(FileCollection::class, 'file_collection_pivot', 'file_id', 'collection_id');
    }
}
