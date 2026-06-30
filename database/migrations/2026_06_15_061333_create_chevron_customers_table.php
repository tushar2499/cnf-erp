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
        Schema::create('chevron_customers', function (Blueprint $table) {
            $table->id();
            $table->string('id_prefix', 20);
            $table->string('customer_id', 30)->unique();
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained('chevron_branches')->nullOnDelete();
            $table->string('owner_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('fax', 30)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('sales_person')->nullable();
            $table->string('customer_account')->nullable();
            $table->string('vat_id')->nullable();
            $table->enum('identity_type', ['BIN', 'TIN', 'NID', 'Other'])->nullable();
            $table->string('tin_bin_nid')->nullable();
            $table->string('contact_person_details')->nullable();
            $table->string('country')->nullable();
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('customer_id_reference')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->enum('pay_type', ['Cash', 'Credit', 'Cheque'])->default('Cash');
            $table->string('portal_password')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('taxscope', ['Exempted', 'Taxable', 'Zero-Rated'])->default('Exempted');
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->integer('limit_days')->default(0);
            $table->decimal('security_deposit', 15, 2)->default(0);
            $table->integer('maturity_days')->default(0);
            $table->string('prefix', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chevron_customers');
    }
};
