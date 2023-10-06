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
        Schema::create('collection_field_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('datatype', [
              // integer
              "tinyInteger",
              "unsignedTinyInteger",
              "smallInteger",
              "unsignedSmallInteger",
              "integer",
              "unsignedInteger",
              "mediumInteger",
              "unsignedMediumInteger",
              "bigInteger",
              "unsignedBigInteger",

              // float
              "decimal",
              "unsignedDecimal",
              "float",
              "double",

              // text
              "char",
              "string",
              "tinyText",
              "text",
              "mediumText",
              "longText",

              //other
              "boolean",
              "date",
              "time",
              "dateTime",
              "timestamp",
            ]);
            $table->timestamps();

            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_field_types');
    }
};
