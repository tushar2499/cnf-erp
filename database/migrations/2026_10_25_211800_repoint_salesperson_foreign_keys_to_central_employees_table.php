<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'nas_freights_freight_bookings',
        'nas_freights_freight_export_bookings',
        'nas_freights_rfqs',
    ];

    public function up(): void
    {
        $this->repoint('employees');
    }

    public function down(): void
    {
        $this->repoint('nas_freights_employees');
    }

    private function repoint(string $employeeTable): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['salesperson_id']);
            });

            Schema::table($table, function (Blueprint $blueprint) use ($employeeTable) {
                $blueprint->foreign('salesperson_id')->references('id')->on($employeeTable)->nullOnDelete();
            });
        }
    }
};
