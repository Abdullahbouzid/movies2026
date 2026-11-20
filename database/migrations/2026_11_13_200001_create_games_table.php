<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 16)->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('meta')->nullable()->comment('short subtitle shown under the title');
            $table->string('color_from')->nullable();
            $table->string('color_to')->nullable();
            $table->string('badge')->nullable()->comment('trending|new|popular');
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
