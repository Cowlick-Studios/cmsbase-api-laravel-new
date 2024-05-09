<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  use HasFactory;

  protected $table = 'settings';

  protected $fillable = [
    'key',
    'value'
  ];

  protected $hidden = [];

  protected $appends = [];

  protected $casts = [
    'value' => 'json'
  ];
}
