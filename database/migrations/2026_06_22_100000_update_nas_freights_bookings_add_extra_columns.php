<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_bookings', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('forfeited_amount', 15, 2)->default(0)->after('discount');
            $table->enum('delivery_status', ['Pending', 'Partially Delivered', 'Fully Delivered'])
                  ->default('Pending')->after('forfeited_amount');
            $table->string('entry_by', 150)->nullable()->after('status');
            $table->string('branch', 100)->nullable()->after('entry_by');
        });

        DB::statement("ALTER TABLE nas_freights_bookings MODIFY COLUMN status ENUM('Draft','Submitted','Approved','Rejected') NOT NULL DEFAULT 'Draft'");
    }

    public function down(): void
    {
        Schema::table('nas_freights_bookings', function (Blueprint $table) {
            $table->dropColumn(['discount', 'forfeited_amount', 'delivery_status', 'entry_by', 'branch']);
        });

        DB::statement("ALTER TABLE nas_freights_bookings MODIFY COLUMN status ENUM('Draft','Submitted','Approved') NOT NULL DEFAULT 'Draft'");
    }
};
