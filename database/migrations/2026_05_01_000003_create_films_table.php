<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('synopsis');
            $table->string('director');
            $table->string('country', 100);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('duration');
            $table->string('genre', 100);
            $table->string('poster_path')->nullable();
            $table->string('trailer_url')->nullable();
            $table->unsignedBigInteger('participant_id');
            // pending, approved, rejected
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->foreign('participant_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
