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
    Schema::create('client_fingerprints', function (Blueprint $table) {
      $table->id();
      $table->string('fingerprint')->unique();
      $table->string('ip');
      $table->text('user_agent');
      $table->string('country_code', 2);
      $table->integer('request_count')->default(0);
      $table->timestamps();

      $table->index(['fingerprint']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('client_fingerprints');
  }
};
