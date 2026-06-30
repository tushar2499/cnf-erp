<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_employees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->date('join_date')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('company_name');
            $table->string('country')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('hs_code', 20)->nullable();
            $table->string('unit', 20)->nullable();
            $table->string('category')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('branch')->nullable();
            $table->string('swift_code', 20)->nullable();
            $table->string('account_no', 50)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_importers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bin_no', 50)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['LC Cost', 'Duty', 'Shipping', 'Other'])->default('Other');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_psi_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_cnf_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_transport_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_ports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->nullable();
            $table->enum('type', ['Sea', 'Air', 'Land'])->default('Sea');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_ports');
        Schema::dropIfExists('nas_trading_transport_companies');
        Schema::dropIfExists('nas_trading_cnf_agents');
        Schema::dropIfExists('nas_trading_psi_companies');
        Schema::dropIfExists('nas_trading_expense_heads');
        Schema::dropIfExists('nas_trading_importers');
        Schema::dropIfExists('nas_trading_banks');
        Schema::dropIfExists('nas_trading_items');
        Schema::dropIfExists('nas_trading_suppliers');
        Schema::dropIfExists('nas_trading_customers');
        Schema::dropIfExists('nas_trading_employees');
    }
};
