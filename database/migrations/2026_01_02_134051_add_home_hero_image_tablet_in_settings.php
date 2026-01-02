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
            $table->text('home_hero_image_tablet')->nullable()->after('home_hero_image_ar');
            $table->text('home_hero_image_tablet_ar')->nullable()->after('home_hero_image_tablet');
            $table->text('home_hero_image_mobile')->nullable()->after('home_hero_image_tablet_ar');
            $table->text('home_hero_image_mobile_ar')->nullable()->after('home_hero_image_mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['home_hero_image_tablet', 'home_hero_image_tablet_ar', 'home_hero_image_mobile', 'home_hero_image_mobile_ar']);
        });
    }
};
