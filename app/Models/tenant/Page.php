<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    'schema' => 'array',
    'data' => 'array'
  ];
}
