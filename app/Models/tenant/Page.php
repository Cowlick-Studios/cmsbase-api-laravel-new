<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
  use HasFactory;

  protected $table = 'pages';

  protected $fillable = [
    'name',
    'schema',
    'data'
  ];

  protected $hidden = [];

  protected $appends = [];

  protected $casts = [
    'data' => 'array'
  ];

  public function fields(): HasMany
  {
    return $this->hasMany(PageField::class, 'page_id', 'id');
  }
}
