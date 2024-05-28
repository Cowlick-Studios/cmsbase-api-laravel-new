<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSubmissionLog extends Model
{
    use HasFactory;

    protected $table = 'email_submission_logs';

	protected $fillable = [
		'email_submission_id',
		'submission_data'
	];

    protected $casts = [
        'submission_data' => 'json',
    ];
}
