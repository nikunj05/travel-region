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
            $table->tinyInteger('about_us')->nullable()->after('content');
            $table->string('founder_image')->nullable()->after('about_us');
            $table->text('why_we_exist')->nullable()->after('founder_image');
            $table->text('our_partners')->nullable()->after('why_we_exist');
            $table->text('few_highlights')->nullable()->after('our_partners');
            $table->string('ready_to_explore_title')->nullable()->after('few_highlights');
            $table->string('ready_to_explore_sub_title')->nullable()->after('ready_to_explore_title');
            $table->string('ready_to_explore_image')->nullable()->after('ready_to_explore_sub_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn([
                'about_us',
                'founder_image',
                'why_we_exist',
                'our_partners',
                'few_highlights',
                'ready_to_explore_title',
                'ready_to_explore_sub_title',
                'ready_to_explore_image'
            ]);
        });
    }
};
