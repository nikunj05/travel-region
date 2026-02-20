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
        Schema::table('hotels', function (Blueprint $table) {
            // index on code and name
            if (!Schema::hasColumn('hotels', 'code')) {
                $table->string('code')->nullable();
            }
            if (!Schema::hasColumn('hotels', 'name')) {
                $table->string('name')->nullable();
            }

            $table->index('code');
            $table->index('name');
        });

        Schema::table('hotel_facilities', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_facilities', 'hotel_code')) {
                $table->string('hotel_code')->nullable();
            }

            $table->index('hotel_code');
        });

        Schema::table('hotel_images', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_images', 'hotel_code')) {
                $table->string('hotel_code')->nullable();
            }

            $table->index('hotel_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropIndex(['name']);
        });

        Schema::table('hotel_facilities', function (Blueprint $table) {
            $table->dropIndex(['hotel_code']);
        });

        Schema::table('hotel_images', function (Blueprint $table) {
            $table->dropIndex(['hotel_code']);
        });
    }
};
