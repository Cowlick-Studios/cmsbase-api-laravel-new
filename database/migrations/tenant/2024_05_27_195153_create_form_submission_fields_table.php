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
        Schema::create('form_submission_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('form_submission_id')->references('id')->on('form_submissions')->onDelete('cascade');
            $table->foreignId('type_id')->references('id')->on('field_types')->onDelete('cascade');
            $table->timestamps();

            $table->index(['name', 'form_submission_id', 'type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submission_fields');
    }
};
