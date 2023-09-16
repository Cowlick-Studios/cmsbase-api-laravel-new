<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    use HasFactory;

    protected $table = 'collections';

		protected $fillable = [
      'name',
      'public',
    ];

    public function fields(): HasMany{
        return $this->hasMany(CollectionField::class, 'collection_id', 'id');
    }
}
