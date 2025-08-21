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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignUuid('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('early_bird', 10, 0);
            $table->date('date_start_early_bird');
            $table->date('date_end_early_bird');
            $table->decimal('normal_price', 10, 0)->nullable();
            $table->date('date_start_normal_price')->nullable();
            $table->date('date_end_normal_price')->nullable();
            $table->decimal('onsite_price', 10, 0);
            $table->date('date_start_onsite_price');
            $table->date('date_end_onsite_price');
            $table->integer('kuota');
            $table->boolean('is_early_bird')->default(true);
            $table->boolean('is_onsite');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
