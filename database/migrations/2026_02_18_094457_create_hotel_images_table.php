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
        Schema::create('hotel_images', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_code');
            $table->text('path');
            $table->string('image_type_code')->nullable();
            $table->integer('order')->nullable();
            $table->integer('visual_order')->nullable();
            $table->string('characteristic_code')->nullable();
            $table->string('room_code')->nullable();
            $table->string('room_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_images');
    }
};
