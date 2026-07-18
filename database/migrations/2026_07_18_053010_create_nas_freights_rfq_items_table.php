<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_rfq_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->enum('item_type', ['container', 'package'])->default('container');
            $table->string('container_size')->nullable();
            $table->string('package_type')->nullable();
            $table->string('hs_code')->nullable();
            $table->string('commodity')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('gross_weight', 10, 2)->nullable();
            $table->enum('weight_unit', ['KG', 'MT'])->default('KG');
            $table->decimal('volume_cbm', 10, 2)->nullable();
            $table->decimal('cargo_value', 15, 2)->nullable();
            $table->string('country_of_origin')->nullable();
            $table->boolean('is_dangerous_goods')->default(false);
            $table->string('special_handling')->nullable();
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('nas_freights_rfqs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_rfq_items');
    }
};
