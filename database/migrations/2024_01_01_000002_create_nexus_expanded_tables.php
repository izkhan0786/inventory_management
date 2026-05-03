<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Units of Measurement
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // pcs, kg, boxes, etc.
            $table->string('short_name');
            $table->timestamps();
        });

        // 3. Batches & Lots
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->string('batch_number');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('initial_qty');
            $table->integer('current_qty');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 4. Serial Numbers
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('serial_number')->unique();
            $table->enum('status', ['Available', 'Sold', 'Returned', 'Scrapped'])->default('Available');
            $table->timestamps();
        });

        // 5. Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['Draft', 'Sent', 'Partially Received', 'Received', 'Cancelled'])->default('Draft');
            $table->date('expected_delivery_date')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('received_quantity')->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
        });

        // 6. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('action'); // create, update, delete
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('payload')->nullable(); // Changes made
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('serial_numbers');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('units');
        Schema::dropIfExists('suppliers');
    }
};
