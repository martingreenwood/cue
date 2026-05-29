<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_site_settings', function (Blueprint $table): void {
            $table->string('basket_membership_upsell')->nullable()->after('customer_basket_label');
        });
    }

    public function down(): void
    {
        Schema::table('public_site_settings', function (Blueprint $table): void {
            $table->dropColumn('basket_membership_upsell');
        });
    }
};
