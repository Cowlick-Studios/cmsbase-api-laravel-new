<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingMailingList extends Model
{
	use HasFactory;

	protected $table = 'marketing_mailing_lists';

	protected $fillable = [
		'name',
		'description',
		'recaptcha',
		'recaptcha_secret',
		'turnstile',
		'turnstile_secret'
	];

	protected $attributes = [
		'recaptcha' => false,
		'recaptcha_secret' => "",
		'turnstile' => false,
		'turnstile_secret' => ""
	];

	public function subscribers(): HasMany
	{
		return $this->hasMany(MarketingMailingListSubscribers::class, 'list_id', 'id');
	}
}
