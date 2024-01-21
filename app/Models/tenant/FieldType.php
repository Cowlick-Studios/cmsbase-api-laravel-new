<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FieldType extends Model
{
  use HasFactory;

  protected $table = 'field_types';

  protected $fillable = [
    'name',
    'datatype',
  ];
}
