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
            $table->text('footer_logo')->nullable()->after('logo');
            $table->text('header_menu_items')->nullable()->after('favicon');
            $table->text('footer_explore_items')->nullable()->after('header_menu_items');
            $table->text('footer_about_items')->nullable()->after('footer_explore_items');
            $table->text('footer_support_items')->nullable()->after('footer_about_items');
            $table->text('copyright')->nullable()->after('footer_support_items');
            $table->text('footer_info')->nullable()->after('copyright');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'footer_logo',
                'header_menu_items',
                'footer_explore_items',
                'footer_about_items',
                'footer_support_items',
                'copyright',
                'footer_info',
            ]);
        });
    }
};
