<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_packages', function (Blueprint $table) {
            // Seats (people) the package can sell per visit slot; 0 = unlimited.
            $table->unsignedSmallInteger('capacity')->default(0)->after('people_count');
        });

        Schema::table('event_packages', function (Blueprint $table) {
            $table->unsignedSmallInteger('capacity')->default(0)->after('people_count');
        });
    }

    public function down(): void
    {
        Schema::table('club_packages', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });

        Schema::table('event_packages', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });
    }
};
