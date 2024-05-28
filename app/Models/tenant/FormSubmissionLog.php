<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmissionLog extends Model
{
    use HasFactory;

    protected $table = 'form_submission_logs';

	protected $fillable = [
		'form_submission_id',
		'submission_data'
	];

    protected $casts = [
        'submission_data' => 'json',
    ];
}
