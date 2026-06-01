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
            $table->string('customer_donate_label')->nullable()->after('basket_membership_upsell');
            $table->string('customer_gift_vouchers_label')->nullable()->after('customer_donate_label');
            $table->string('customer_memberships_label')->nullable()->after('customer_gift_vouchers_label');
        });
    }

    public function down(): void
    {
        Schema::table('public_site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_donate_label',
                'customer_gift_vouchers_label',
                'customer_memberships_label',
            ]);
        });
    }
};
