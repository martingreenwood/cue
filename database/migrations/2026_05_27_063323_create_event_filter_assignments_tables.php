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
        Schema::create('event_what_term', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filter_term_id')->constrained()->cascadeOnDelete();

            $table->unique(['event_id', 'filter_term_id']);
        });

        Schema::create('event_offer_term', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filter_term_id')->constrained()->cascadeOnDelete();

            $table->unique(['event_id', 'filter_term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_offer_term');
        Schema::dropIfExists('event_what_term');
    }
};
