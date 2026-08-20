<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }
};
