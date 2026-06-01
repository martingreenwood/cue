<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->foreignId('sync_run_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('future_on_sale_total');
            $table->unsignedInteger('future_on_sale_available');
            $table->unsignedInteger('future_on_sale_stale');
            $table->unsignedInteger('future_on_sale_unpriced');
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['provider', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_snapshots');
    }
};
