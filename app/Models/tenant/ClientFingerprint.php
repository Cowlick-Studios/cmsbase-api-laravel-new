<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientFingerprint extends Model
{
  use HasFactory;

  protected $table = 'client_fingerprints';

  protected $fillable = [
    'fingerprint',
  ];

  public function logs(): HasMany
  {
    return $this->hasMany(ClientRequestLog::class, 'fingerprint', 'fingerprint');
  }
}
