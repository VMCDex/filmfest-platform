<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('film_id');
            // sha256 от IP-адреса: один голос за фильм с одного адреса
            $table->string('ip_hash', 64);

            $table->unique(['film_id', 'ip_hash']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('film_id')->references('id')->on('films')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
