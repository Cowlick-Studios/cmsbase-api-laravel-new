<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmailSubmission extends Model
{
    use HasFactory;

    protected $table = 'email_submissions';

		protected $fillable = [
      'name'
    ];

    // recipients
    public function recipients(): BelongsToMany {
      return $this->belongsToMany(User::class, 'email_submission_recipient_pivot', 'email_submission_id', 'user_id');
    }

    // fields
    public function fields(): HasMany{
      return $this->hasMany(EmailSubmissionField::class, 'email_submission_id', 'id');
    }
}
