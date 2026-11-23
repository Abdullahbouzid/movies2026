<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('video')->nullable()->after('image');
            $table->string('trailer_url')->nullable()->after('video');
        });

        Schema::table('showtimes', function (Blueprint $table) {
            $table->date('show_date')->nullable()->after('movie_id')->index();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->unsignedSmallInteger('devices_count')->default(1)->after('is_active');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('qr_path')->nullable()->after('price');
        });

        Schema::table('game_bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('device_number')->nullable()->after('play_time_id');
            $table->string('qr_path')->nullable()->after('price');
            $table->unique(['game_id', 'play_date', 'play_time_id', 'device_number'], 'game_bookings_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('game_bookings', function (Blueprint $table) {
            $table->dropUnique('game_bookings_slot_unique');
            $table->dropColumn(['device_number', 'qr_path']);
        });

        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('qr_path'));

        Schema::table('games', fn (Blueprint $table) => $table->dropColumn('devices_count'));

        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropIndex(['show_date']);
            $table->dropColumn('show_date');
        });

        Schema::table('movies', fn (Blueprint $table) => $table->dropColumn(['video', 'trailer_url']));
    }
};
