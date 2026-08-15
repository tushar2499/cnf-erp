<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('module');
            $table->integer('sorting_order')->default(0);
            $table->timestamps();

            $table->unique(['name', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
