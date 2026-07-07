<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chevron_employees', function (Blueprint $table) {
            $table->enum('type', ['team_leader', 'prepare'])->default('prepare')->after('is_active');
            $table->foreignId('team_leader_id')->nullable()->after('type')
                ->constrained('chevron_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chevron_employees', function (Blueprint $table) {
            $table->dropForeign(['team_leader_id']);
            $table->dropColumn(['type', 'team_leader_id']);
        });
    }
};
