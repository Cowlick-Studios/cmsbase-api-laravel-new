<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\tenant\User;

class RequestLog extends Model
{
    use HasFactory;

    protected $table = 'request_logs';

		protected $fillable = [
      'user_id',
      'user_agent',
      'url',
      'method',
      'status'
    ];

    protected $hidden = [];

    protected $appends = [];

    public function user(): BelongsTo{
      return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
