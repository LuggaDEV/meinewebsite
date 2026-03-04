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
        Schema::table('recipe_reviews', function (Blueprint $table) {
            $table->dropUnique(['recipe_id', 'user_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('recipe_reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('recipe_reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['recipe_id', 'user_id']);
        });
    }
};
