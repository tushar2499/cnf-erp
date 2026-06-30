<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_services', function (Blueprint $table) {
            $table->dropColumn(['category', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('chevron_services', function (Blueprint $table) {
            $table->enum('category', ['import', 'export', 'both'])->default('both')->after('name');
            $table->text('description')->nullable()->after('category');
        });
    }
};
