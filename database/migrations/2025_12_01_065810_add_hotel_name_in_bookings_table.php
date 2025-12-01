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
            $table->string('hotel_name')->nullable()->after('hotel_code');
            $table->string('hotel_location')->nullable()->after('hotel_name');
            $table->text('hotel_images')->nullable()->after('hotel_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['hotel_name', 'hotel_location', 'hotel_images']);
        });
    }
};
