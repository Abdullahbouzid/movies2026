<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['bookings', 'game_bookings', 'club_bookings', 'event_bookings'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->timestamp('paid_at')->nullable()->after('payment_status');
                $blueprint->index('paid_at', $table.'_paid_at_index');
            });
        }

        // Bookings already marked paid have no cash-in timestamp; the booking
        // time is the closest honest approximation for existing rows.
        foreach (self::TABLES as $table) {
            DB::table($table)
                ->where('payment_status', 'paid')
                ->whereNull('paid_at')
                ->update(['paid_at' => DB::raw('created_at')]);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex($table.'_paid_at_index');
                $blueprint->dropColumn('paid_at');
            });
        }
    }
};
