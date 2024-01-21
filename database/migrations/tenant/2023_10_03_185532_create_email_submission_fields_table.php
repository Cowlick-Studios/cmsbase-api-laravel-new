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
    Schema::create('email_submission_fields', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->foreignId('email_submission_id')->references('id')->on('email_submissions')->onDelete('cascade');
      $table->foreignId('type_id')->references('id')->on('field_types')->onDelete('cascade');
      $table->timestamps();

      $table->index(['name', 'email_submission_id', 'type_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_submission_fields');
  }
};
