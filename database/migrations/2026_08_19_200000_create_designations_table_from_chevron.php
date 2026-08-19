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

        // Re-point employees.designation_id FK from chevron_designations to designations
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('employees_designation_id_foreign');
            $table->foreign('designation_id')->references('id')->on('designations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['designation_id']);
            $table->foreign('designation_id')->references('id')->on('chevron_designations')->nullOnDelete();
        });

        Schema::dropIfExists('designations');
    }
};
