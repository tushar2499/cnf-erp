<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_booking_items', function (Blueprint $table) {
            $table->decimal('net_weight', 10, 3)->nullable()->after('quantity');
            $table->decimal('chargeable_weight', 10, 3)->nullable()->after('gross_weight');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_booking_items', function (Blueprint $table) {
            $table->dropColumn(['net_weight', 'chargeable_weight']);
        });
    }
};
