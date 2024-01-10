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
    Schema::create('client_request_logs', function (Blueprint $table) {
      $table->id();
      $table->string('fingerprint');
      $table->string('ip');
      $table->text('user_agent');
      $table->text('url');
      $table->string('country_code');
      $table->timestamps();

      $table->index(['fingerprint', 'id', 'url']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('client_request_logs');
  }
};
