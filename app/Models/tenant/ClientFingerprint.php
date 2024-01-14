<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\tenant\ClientAnalytic;

class ClientFingerprint extends Model
{
  use HasFactory;

  protected $table = 'client_fingerprints';

  protected $fillable = [
    'fingerprint',
    'ip',
    'user_agent',
    'country_code',
    'request_count'
  ];

  public function analytics(): BelongsToMany
  {
    return $this->belongsToMany(ClientAnalytic::class, 'client_analytic_fingerprints_pivot', 'fingerprint_id', 'analytic_id');
  }
}
