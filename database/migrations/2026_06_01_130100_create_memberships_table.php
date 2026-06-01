<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('external_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('html_description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('source_payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['provider', 'is_visible', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
