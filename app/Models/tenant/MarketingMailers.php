<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingMailers extends Model
{
	use HasFactory;

	protected $table = 'marketing_mailers';

	protected $fillable = [
		'name',
		'subject',
		'unlayer_data',
		'html'
	];

	protected $casts = [
			'unlayer_data' => 'json',
	];
}
