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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('category')->nullable()->after('hotel_location');
            $table->decimal('net_total_price', 10, 2)->nullable()->after('currency');
            $table->string('net_currency')->nullable()->after('net_total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['category', 'net_total_price', 'net_currency']);
        });
    }
};
