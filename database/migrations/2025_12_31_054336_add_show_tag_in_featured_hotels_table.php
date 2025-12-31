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
        Schema::table('featured_hotels', function (Blueprint $table) {
            $table->boolean('show_tag')->default(false)->after('hotel_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_hotels', function (Blueprint $table) {
            $table->dropColumn('show_tag');
        });
    }
};
