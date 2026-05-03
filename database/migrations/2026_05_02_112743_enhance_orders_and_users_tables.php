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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('type')->default('Sales Order')->after('order_number');
            $table->string('currency')->default('USD')->after('total_amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('total_amount');
            $table->text('notes')->nullable()->after('currency');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Staff')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['type', 'currency', 'tax_amount', 'discount_amount', 'notes']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
