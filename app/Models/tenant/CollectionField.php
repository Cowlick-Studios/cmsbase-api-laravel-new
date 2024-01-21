<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CollectionField extends Model
{
  use HasFactory;

  protected $table = 'collection_fields';

  protected $fillable = [
    'collection_id',
    'type_id',
    'name'
  ];

  public function type(): BelongsTo
  {
    return $this->belongsTo(FieldType::class, 'type_id', 'id');
  }
}
