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
        Schema::table('performances', function (Blueprint $table) {
            $table->unsignedBigInteger('display_from_price_minor')->nullable()->after('is_cancelled');
            $table->char('display_currency', 3)->nullable()->after('display_from_price_minor');
            $table->boolean('has_dynamic_pricing')->default(false)->after('display_currency');
            $table->timestampTz('prices_synced_at')->nullable()->after('has_dynamic_pricing')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->dropColumn([
                'display_from_price_minor',
                'display_currency',
                'has_dynamic_pricing',
                'prices_synced_at',
            ]);
        });
    }
};
