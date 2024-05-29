<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingMailingListSubscribers extends Model
{
    use HasFactory;

    protected $table = 'marketing_mailing_list_subscribers';

	protected $fillable = [
		'list_id',
		'email'
	];
}
