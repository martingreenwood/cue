<?php

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
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('external_id');
            $table->string('web_id')->nullable();
            $table->string('external_plan_id')->nullable();
            $table->string('external_price_list_id')->nullable();
            $table->timestampTz('starts_at')->index();
            $table->timestampTz('sales_start_at')->nullable();
            $table->timestampTz('sales_end_at')->nullable();
            $table->boolean('is_on_sale')->default(false)->index();
            $table->boolean('is_cancelled')->default(false)->index();
            $table->json('source_payload');
            $table->timestampTz('synced_at')->index();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['event_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performances');
    }
};
