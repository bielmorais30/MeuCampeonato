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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained('championships')->onDelete('cascade');
            $table->enum('phase', ['quarter', 'semi', 'third_place', 'final'])->default('quarter');
            $table->integer('order')->nullable();
            $table->foreignId('team_home_id')->nullable()->constrained('teams');
            $table->foreignId('team_away_id')->nullable()->constrained('teams');
            $table->integer('goals_home')->nullable();
            $table->integer('goals_away')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('teams');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
