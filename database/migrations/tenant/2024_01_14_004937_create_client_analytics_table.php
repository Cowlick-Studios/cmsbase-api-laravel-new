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
    Schema::create('client_analytics', function (Blueprint $table) {
      $table->id();
      $table->date('date');
      $table->integer('request_count')->default(0);
      $table->timestamps();

      $table->index(['date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('client_analytics');
  }
};
