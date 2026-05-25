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
        Schema::create('performance_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('external_id');
            $table->string('ticket_type_external_id');
            $table->string('ticket_type_name');
            $table->string('price_band_external_id');
            $table->string('price_band_name');
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->boolean('is_band_default')->default(false);
            $table->boolean('is_dynamic_pricing_eligible')->default(false);
            $table->json('source_payload');
            $table->timestampTz('synced_at')->index();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['performance_id', 'is_band_default', 'amount_minor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_prices');
    }
};
