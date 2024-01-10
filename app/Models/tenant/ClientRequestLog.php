<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRequestLog extends Model
{
  use HasFactory;

  protected $table = 'client_request_logs';

  protected $fillable = [
    'fingerprint',
    'ip',
    'user_agent',
    'url',
    'country_code'
  ];
}
