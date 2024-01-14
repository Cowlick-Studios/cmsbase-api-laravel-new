<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('client_analytic_fingerprints_pivot', function (Blueprint $table) {
      $table->foreignId('fingerprint_id')->references('id')->on('client_fingerprints');
      $table->foreignId('analytic_id')->references('id')->on('client_analytics');
      $table->integer('request_count')->default(0);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('client_analytic_fingerprints_pivot');
  }
};
