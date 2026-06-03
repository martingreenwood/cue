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
        Schema::table(config('filament-fabricator.table_name', 'pages'), function (Blueprint $table) {
            $table->string('featured_image_path')->nullable()->after('parent_id');
            $table->string('featured_image_alt')->nullable()->after('featured_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('filament-fabricator.table_name', 'pages'), function (Blueprint $table) {
            $table->dropColumn(['featured_image_path', 'featured_image_alt']);
        });
    }
};
