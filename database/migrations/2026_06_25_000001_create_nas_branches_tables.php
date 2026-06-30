<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_trading_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add branch_id to NAS Freights operation tables
        foreach ([
            'nas_freights_bookings',
            'nas_freights_customer_bills',
            'nas_freights_supplier_bills',
            'nas_freights_money_receipts',
            'nas_freights_supplier_payments',
        ] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });
        }

        // Add branch_id to NAS Trading operation tables
        foreach ([
            'nas_trading_lcs',
            'nas_trading_shipments',
            'nas_trading_customer_bills',
            'nas_trading_deliveries',
            'nas_trading_money_receipts',
        ] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'nas_freights_bookings', 'nas_freights_customer_bills',
            'nas_freights_supplier_bills', 'nas_freights_money_receipts',
            'nas_freights_supplier_payments',
        ] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) { $table->dropColumn('branch_id'); });
        }
        foreach ([
            'nas_trading_lcs', 'nas_trading_shipments', 'nas_trading_customer_bills',
            'nas_trading_deliveries', 'nas_trading_money_receipts',
        ] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) { $table->dropColumn('branch_id'); });
        }
        Schema::dropIfExists('nas_freights_branches');
        Schema::dropIfExists('nas_trading_branches');
    }
};
