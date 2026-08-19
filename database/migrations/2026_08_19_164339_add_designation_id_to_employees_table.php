<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('designation_id')->nullable()->after('code');
            $table->foreign('designation_id')->references('id')->on('chevron_designations')->nullOnDelete();
            $table->dropColumn('designation');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['designation_id']);
            $table->dropColumn('designation_id');
            $table->string('designation')->nullable()->after('code');
        });
    }
};
