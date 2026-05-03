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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('subscription_expires_at')->nullable();
            $table->string('plan_type')->default('basic'); // basic, pro, enterprise
            $table->boolean('is_suspended')->default(false);
            $table->string('subscription_status')->default('trial'); // active, expired, trial
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_expires_at', 'plan_type', 'is_suspended', 'subscription_status']);
        });
    }
};
