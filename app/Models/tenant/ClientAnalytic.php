<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\tenant\ClientFingerprint;
use App\Models\tenant\ClientAnalyticCountry;

class ClientAnalytic extends Model
{
  use HasFactory;

  protected $table = 'client_analytics';

  protected $fillable = [
    'date',
    'request_count'
  ];

  public function fingerprints(): BelongsToMany
  {
    return $this->belongsToMany(ClientFingerprint::class, 'client_analytic_fingerprints_pivot', 'analytic_id', 'fingerprint_id');
  }

  public function countryAnalytics(): HasMany
  {
    return $this->hasMany(ClientAnalyticCountry::class, 'client_analytic_id', 'id');
  }
}
