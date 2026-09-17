<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->dropColumn(['item_id', 'goods_name']);
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->after('customer_id');
            $table->string('goods_name')->nullable()->after('item_id');
        });
    }
};
