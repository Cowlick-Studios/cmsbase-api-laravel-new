<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\tenant\User;
use App\Models\tenant\FormSubmissionField;
use App\Models\tenant\FormSubmissionLog;

class FormSubmission extends Model
{
    use HasFactory;

    protected $table = 'form_submissions';

		protected $fillable = [
      'name',
      'origin',
      'log',
      'send_mail',
      'recaptcha',
      'turnstile',
      'recaptcha_secret',
      'turnstile_secret'
    ];

    // recipients
    public function recipients(): BelongsToMany {
      return $this->belongsToMany(User::class, 'form_submission_recipiant_pivot', 'form_submission_id', 'user_id');
    }

    // fields
    public function fields(): HasMany{
      return $this->hasMany(FormSubmissionField::class, 'form_submission_id', 'id');
    }

    // logs
    public function logs(): HasMany{
      return $this->hasMany(FormSubmissionLog::class, 'form_submission_id', 'id');
    }
}
