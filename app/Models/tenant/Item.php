<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
  use HasFactory;

  protected $table = 'items';

  protected $fillable = [
    'name',
    'type_id',
    'value',
    'published'
  ];

  protected $casts = [
    'data' => 'array'
  ];

  public function type(): BelongsTo
  {
    return $this->belongsTo(CollectionFieldType::class, 'type_id', 'id');
  }
}
