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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('five_star_commission')->nullable()->after('home_hero_image');
            $table->string('four_star_commission')->nullable()->after('five_star_commission');
            $table->string('three_star_commission')->nullable()->after('four_star_commission');
            $table->string('two_star_commission')->nullable()->after('three_star_commission');
            $table->string('one_star_commission')->nullable()->after('two_star_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'five_star_commission',
                'four_star_commission',
                'three_star_commission',
                'two_star_commission',
                'one_star_commission',
            ]);
        });
    }
};
