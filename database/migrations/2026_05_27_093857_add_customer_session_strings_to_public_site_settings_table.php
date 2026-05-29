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
        Schema::table('public_site_settings', function (Blueprint $table): void {
            $table->string('customer_logged_in_label')->nullable();
            $table->string('customer_logged_out_label')->nullable();
            $table->string('customer_basket_label')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_logged_in_label',
                'customer_logged_out_label',
                'customer_basket_label',
            ]);
        });
    }
};
