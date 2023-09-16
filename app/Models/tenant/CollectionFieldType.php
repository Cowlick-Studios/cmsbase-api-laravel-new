<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CollectionFieldType extends Model
{
    use HasFactory;

    protected $table = 'collection_field_types';

		protected $fillable = [
      'name',
      'datatype',
    ];
}
