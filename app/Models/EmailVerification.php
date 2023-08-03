<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;

    protected $table = 'email_verifications';

		protected $fillable = [
      'user_id',
      'code'
    ];

    protected $hidden = [];

    protected $appends = [];
}
