<?php

declare(strict_types=1);

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
        Schema::table('sync_runs', function (Blueprint $table) {
            $table->unsignedInteger('performances_queued')->default(0)->after('performances_synced');
            $table->unsignedInteger('performances_failed')->default(0)->after('performances_queued');
            $table->unsignedInteger('prices_synced')->default(0)->after('performances_failed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_runs', function (Blueprint $table) {
            $table->dropColumn([
                'performances_queued',
                'performances_failed',
                'prices_synced',
            ]);
        });
    }
};
