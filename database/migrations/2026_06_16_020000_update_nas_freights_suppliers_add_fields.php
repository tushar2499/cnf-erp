<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_suppliers', function (Blueprint $table) {
            $table->string('phone_no', 30)->nullable()->after('address');
            $table->string('fax', 30)->nullable()->after('phone_no');
            $table->string('url', 255)->nullable()->after('fax');
            $table->string('contact', 255)->nullable()->after('mobile_no');
            $table->string('designation', 255)->nullable()->after('contact');
            $table->string('supplier_group', 50)->nullable()->after('designation');
            $table->string('taxscope', 20)->default('Exempted')->after('supplier_group');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_suppliers', function (Blueprint $table) {
            $table->dropColumn(['phone_no', 'fax', 'url', 'contact', 'designation', 'supplier_group', 'taxscope']);
        });
    }
};
