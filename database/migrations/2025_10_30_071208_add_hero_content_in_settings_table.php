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
            $table->text('home_title')->nullable()->after('social_media_links');
            $table->text('home_subtitle')->nullable()->after('home_title');
            $table->text('home_hero_image')->nullable()->after('home_subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['home_title', 'home_subtitle', 'home_hero_image']);
        });
    }
};
