<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'nas_freights_customers',
            'nas_freights_suppliers',
            'nas_freights_vehicles',
            'nas_freights_employees',
        ] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'nas_freights_customers',
            'nas_freights_suppliers',
            'nas_freights_vehicles',
            'nas_freights_employees',
        ] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropColumn('branch_id');
            });
        }
    }
};
