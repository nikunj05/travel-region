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
        Schema::table('testimonials', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('location')->change();
            $table->text('message')->change();
            $table->text('hotel')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('location')->change();
            $table->string('message')->change();
            $table->string('hotel')->change();
        });
    }
};
