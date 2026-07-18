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
        Schema::create('nas_freights_container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();          // e.g. 20GP, 40HC
            $table->string('description')->nullable();     // e.g. 20ft General Purpose
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_freights_container_types');
    }
};
