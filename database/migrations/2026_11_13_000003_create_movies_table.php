<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration')->comment('minutes');
            $table->decimal('rating', 3, 1)->default(0);
            $table->string('status')->default('available')->comment('available|coming_soon');
            $table->date('release_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
