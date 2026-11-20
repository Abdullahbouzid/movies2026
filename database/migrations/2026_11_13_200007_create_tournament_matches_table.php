<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->unsignedInteger('round')->comment('1 = first round');
            $table->string('round_label')->nullable()->comment('e.g. نصف النهائي');
            $table->unsignedInteger('position')->comment('order inside the round');
            $table->foreignId('player_one_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->foreignId('player_two_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->unsignedInteger('score_one')->nullable();
            $table->unsignedInteger('score_two')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
};
