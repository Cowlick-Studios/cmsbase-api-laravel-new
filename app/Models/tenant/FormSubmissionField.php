<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionField extends Model
{
  use HasFactory;

  protected $table = 'form_submission_fields';

  protected $fillable = [
    'form_submission_id',
    'type_id',
    'name'
  ];

  public function type(): BelongsTo
  {
    return $this->belongsTo(FieldType::class, 'type_id', 'id');
  }
}
