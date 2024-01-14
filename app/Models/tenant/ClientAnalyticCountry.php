<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\tenant\ClientAnalytic;

class ClientAnalyticCountry extends Model
{
  use HasFactory;

  protected $table = 'client_analytic_countries';

  protected $fillable = [
    'country_code',
    'request_count'
  ];

  public function analytic(): BelongsTo
  {
    return $this->belongsTo(ClientAnalytic::class, 'client_analytic_id', 'id');
  }
}
