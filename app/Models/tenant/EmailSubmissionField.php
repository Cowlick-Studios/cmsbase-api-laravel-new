<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSubmissionField extends Model
{
  use HasFactory;

  protected $table = 'email_submission_fields';

  protected $fillable = [
    'email_submission_id',
    'type_id',
    'name'
  ];

  public function type(): BelongsTo
  {
    return $this->belongsTo(FieldType::class, 'type_id', 'id');
  }
}
