<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FileCollection extends Model
{
    use HasFactory;

    protected $table = 'file_collections';

		protected $fillable = [
      'name',
    ];

    protected $hidden = [];

    protected $appends = [];

    public function files(): BelongsToMany {
      return $this->belongsToMany(File::class, 'file_collection_pivot', 'collection_id', 'file_id');
    }
}
