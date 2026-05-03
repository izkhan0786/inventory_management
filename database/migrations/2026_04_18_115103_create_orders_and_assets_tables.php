<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sales Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->enum('status', ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'])->default('Pending');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained(); // Agent/Admin who created it
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 2. Equipment & Fixed Assets
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->enum('status', ['In Use', 'In Maintenance', 'Available', 'Retired', 'Broken'])->default('Available');
            $table->string('location')->nullable();
            $table->foreignId('user_id')->nullable()->constrained(); // Assigned to
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Transactions (Financial tracking separate from stock)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->enum('type', ['INCOME', 'EXPENSE']);
            $table->decimal('amount', 15, 2);
            $table->string('category')->nullable(); // Sale, Rent, Salary, etc.
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
