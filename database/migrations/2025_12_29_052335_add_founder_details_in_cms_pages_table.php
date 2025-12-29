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
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->text('founder_title')->nullable()->after('founder_image');
            $table->text('founder_name')->nullable()->after('founder_title');
            $table->text('founder_designation')->nullable()->after('founder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn(['founder_title', 'founder_name', 'founder_designation']);
        });
    }
};
