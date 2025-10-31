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
        Schema::table('popular_destinations', function (Blueprint $table) {
            $table->integer('hotel_count')->nullable()->after('longitude');
            $table->string('hotel_min_price')->nullable()->after('hotel_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popular_destinations', function (Blueprint $table) {
            $table->dropColumn(['hotel_count', 'hotel_min_price']);
        });
    }
};
