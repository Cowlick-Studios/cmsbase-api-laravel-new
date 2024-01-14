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
    Schema::create('client_analytic_countries', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_analytic_id')->references('id')->on('client_analytics');
      $table->string('country_code', 2);
      $table->integer('request_count')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('client_analytic_countrys');
  }
};
