<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->unsignedInteger('ratings_count')->default(0)->after('rating');
            $table->string('badge')->nullable()->after('status')->comment('trending|new|popular');
            $table->string('poster_from')->nullable()->after('image');
            $table->string('poster_to')->nullable()->after('poster_from');
            $table->string('accent_icon')->nullable()->after('poster_to');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['ratings_count', 'badge', 'poster_from', 'poster_to', 'accent_icon']);
        });
    }
};
