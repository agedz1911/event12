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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('id_participant');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->unsignedBigInteger('nik')->nullable();
            $table->json('participant_type');
            $table->string('title_specialist')->nullable();
            $table->string('speciality')->nullable();
            $table->string('title')->nullable();
            $table->string('name_on_certificate')->nullable();
            $table->string('institution')->nullable();
            $table->string('email');
            $table->unsignedBigInteger('phone_number');
            $table->string('country');
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->integer('postal_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
