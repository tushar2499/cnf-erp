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
        Schema::create('nas_freights_package_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();      // e.g. Carton, Pallet
            $table->string('description')->nullable();  // e.g. Standard cardboard carton
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
        Schema::dropIfExists('nas_freights_package_types');
    }
};
