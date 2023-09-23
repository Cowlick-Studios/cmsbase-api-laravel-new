<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileCollection extends Model
{
    use HasFactory;

    protected $table = 'file_collections';

		protected $fillable = [
      'name',
    ];

    protected $hidden = [];

    protected $appends = [];

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'collection_id', 'id');
    }
}
