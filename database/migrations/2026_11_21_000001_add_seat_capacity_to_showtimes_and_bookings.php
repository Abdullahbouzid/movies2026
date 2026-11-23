<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('showtimes', function (Blueprint $table) {
            $table->unsignedInteger('seats_capacity')->default(60)->after('end_time');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Seats consumed by this booking, snapshotted from the package.
            $table->unsignedTinyInteger('seats')->default(1)->after('package_id');
            $table->index(['showtime_id', 'show_date']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['showtime_id', 'show_date']);
            $table->dropColumn('seats');
        });

        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropColumn('seats_capacity');
        });
    }
};
