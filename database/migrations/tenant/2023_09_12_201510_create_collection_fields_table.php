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
    Schema::create('collection_fields', function (Blueprint $table) {
      $table->id();
      $table->foreignId('collection_id')->references('id')->on('collections')->onDelete('cascade');
      $table->foreignId('type_id')->references('id')->on('collection_field_types')->onDelete('cascade');
      $table->string('name');
      $table->timestamps();

      $table->index(['name', 'collection_id', 'type_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('collection_fields');
  }
};
