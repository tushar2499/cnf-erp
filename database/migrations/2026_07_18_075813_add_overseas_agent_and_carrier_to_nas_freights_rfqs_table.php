<?php

use App\Models\NasFreights\NasFreightsOverseasAgent;
use App\Models\NasFreights\NasFreightsShippingCarrier;
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
        Schema::table('nas_freights_rfqs', function (Blueprint $table) {
            $table->foreignId('overseas_agent_id')->nullable()->after('salesperson_id')
                ->constrained('nas_freights_overseas_agents')->nullOnDelete();
            $table->foreignId('shipping_carrier_id')->nullable()->after('overseas_agent_id')
                ->constrained('nas_freights_shipping_carriers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_rfqs', function (Blueprint $table) {
            $table->dropForeignIdFor(NasFreightsOverseasAgent::class, 'overseas_agent_id');
            $table->dropForeignIdFor(NasFreightsShippingCarrier::class, 'shipping_carrier_id');
            $table->dropColumn(['overseas_agent_id', 'shipping_carrier_id']);
        });
    }
};
