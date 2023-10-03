<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userPasswordReset extends Model
{
    use HasFactory;

    protected $table = 'user_password_reset';

		protected $fillable = [
      'email',
      'verification_code',
      'new_password'
    ];

    protected $hidden = [];

    protected $appends = [];
}
