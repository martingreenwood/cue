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
        Schema::create('public_site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('listing_kicker')->nullable();
            $table->string('guide_price_label')->nullable();
            $table->string('guide_price_prefix')->nullable();
            $table->string('prices_confirmed_in_booking')->nullable();
            $table->string('dynamic_price_suffix')->nullable();
            $table->string('stale_price_suffix')->nullable();
            $table->text('performance_freshness_notice')->nullable();
            $table->string('booking_cta_label')->nullable();
            $table->string('online_booking_unavailable_label')->nullable();
            $table->text('secure_booking_prefix')->nullable();
            $table->text('footer_availability_notice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_site_settings');
    }
};
