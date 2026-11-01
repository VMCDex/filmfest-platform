<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jury_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jury_id');
            $table->unsignedBigInteger('film_id');
            $table->decimal('criterion_script', 4, 2);
            $table->decimal('criterion_director', 4, 2);
            $table->decimal('criterion_acting', 4, 2);
            $table->decimal('criterion_cinematography', 4, 2);
            $table->decimal('criterion_sound', 4, 2);
            // среднее по пяти критериям, считается в модели JuryScore
            $table->decimal('total_score', 4, 2)->nullable();

            $table->unique(['jury_id', 'film_id']);
            $table->foreign('jury_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('film_id')->references('id')->on('films')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jury_scores');
    }
};
