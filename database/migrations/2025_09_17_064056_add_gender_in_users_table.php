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
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('remember_token');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('date_of_birth');
            $table->string('address')->nullable()->after('nationality');
            $table->string('passport_number')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'date_of_birth', 'nationality', 'address', 'passport_number']);
        });
    }
};
