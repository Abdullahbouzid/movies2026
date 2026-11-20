<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('participants_count')->default(0)->comment('planned number of participants');
            $table->string('format')->default('knockout')->comment('knockout|league|groups');
            $table->string('status')->default('upcoming')->comment('upcoming|open|drawn|running|finished');
            $table->decimal('entry_fee', 10, 2)->default(0);
            $table->decimal('prize', 10, 2)->default(0);
            $table->date('starts_on')->nullable();
            $table->time('starts_at')->nullable();
            $table->timestamp('drawn_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
