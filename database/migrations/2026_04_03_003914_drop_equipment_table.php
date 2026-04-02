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
        Schema::dropIfExists('equipment');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('link');
            $table->string('category');
            $table->string('price')->nullable();
            $table->string('original_price')->nullable();
            $table->timestamp('last_price_checked_at')->nullable();
            $table->string('discount_percentage', 10)->nullable();
            $table->timestamps();
        });
    }
};
