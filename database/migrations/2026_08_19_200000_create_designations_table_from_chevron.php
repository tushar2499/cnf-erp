<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Copy all rows preserving IDs
        DB::statement('INSERT INTO designations (id, name, is_active, created_at, updated_at) SELECT id, name, is_active, created_at, updated_at FROM chevron_designations');
    }

    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
};
