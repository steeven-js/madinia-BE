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
        Schema::table('events', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('firebaseId');
            $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
            $table->decimal('price', 10, 2)->nullable()->after('stripe_price_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['stripe_product_id', 'stripe_price_id', 'price']);
        });
    }
};
