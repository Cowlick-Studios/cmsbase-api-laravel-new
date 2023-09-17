<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegister extends Model
{
    use HasFactory;

    protected $table = 'user_register';

		protected $fillable = [
      'email',
      'verification_code'
    ];

    protected $hidden = [];

    protected $appends = [];
}
