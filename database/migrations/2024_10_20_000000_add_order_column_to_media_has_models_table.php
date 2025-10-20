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
        Schema::table('media_has_models', function (Blueprint $table) {
            $table->unsignedInteger('order_column')->nullable()->after('media_id');
            $table->index('order_column');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_has_models', function (Blueprint $table) {
            $table->dropIndex(['order_column']);
            $table->dropColumn('order_column');
        });
    }
};
