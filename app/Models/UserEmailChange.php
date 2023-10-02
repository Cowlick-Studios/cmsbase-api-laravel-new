<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmailChange extends Model
{
    use HasFactory;

    protected $table = 'user_email_change';

		protected $fillable = [
      'email',
      'verification_code_old',
      'new_email',
      'verification_code_new'
    ];

    protected $hidden = [];

    protected $appends = [];
}
