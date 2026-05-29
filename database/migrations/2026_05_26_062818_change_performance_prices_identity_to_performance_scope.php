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
        Schema::table('performance_prices', function (Blueprint $table) {
            $table->dropUnique('performance_prices_provider_external_id_unique');
            $table->unique(
                ['performance_id', 'provider', 'external_id'],
                'performance_prices_performance_provider_external_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_prices', function (Blueprint $table) {
            $table->dropUnique('performance_prices_performance_provider_external_unique');
            $table->unique(['provider', 'external_id']);
        });
    }
};
