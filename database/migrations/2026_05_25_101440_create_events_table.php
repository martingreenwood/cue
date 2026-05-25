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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('external_id');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('description_html')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('image_url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->text('image_alt')->nullable();
            $table->boolean('is_on_sale')->default(false)->index();
            $table->timestampTz('first_performance_at')->nullable()->index();
            $table->timestampTz('last_performance_at')->nullable()->index();
            $table->json('source_payload');
            $table->timestampTz('synced_at')->index();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
