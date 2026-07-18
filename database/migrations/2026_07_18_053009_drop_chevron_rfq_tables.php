<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('chevron_rfq_items');
        Schema::dropIfExists('chevron_rfqs');
    }

    public function down(): void
    {
        Schema::create('chevron_rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_no')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('rfq_date');
            $table->date('valid_until')->nullable();
            $table->enum('type', ['import', 'export'])->default('import');
            $table->enum('service_type', ['FCL', 'LCL', 'Air', 'Truck'])->default('FCL');
            $table->string('incoterms')->nullable();
            $table->string('currency')->default('BDT');
            $table->unsignedBigInteger('pol_id')->nullable();
            $table->unsignedBigInteger('pod_id')->nullable();
            $table->string('place_of_receipt')->nullable();
            $table->string('place_of_delivery')->nullable();
            $table->text('commodity_description')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['Draft', 'Pending', 'Win', 'Lose'])->default('Draft');
            $table->string('lost_reason')->nullable();
            $table->unsignedBigInteger('converted_job_id')->nullable();
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('chevron_customers')->nullOnDelete();
            $table->foreign('pol_id')->references('id')->on('chevron_ports')->nullOnDelete();
            $table->foreign('pod_id')->references('id')->on('chevron_ports')->nullOnDelete();
            $table->foreign('converted_job_id')->references('id')->on('chevron_jobs')->nullOnDelete();
            $table->foreign('salesperson_id')->references('id')->on('chevron_employees')->nullOnDelete();
        });

        Schema::create('chevron_rfq_items', function (Blueprint $table) {
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

            $table->foreign('rfq_id')->references('id')->on('chevron_rfqs')->cascadeOnDelete();
        });
    }
};
