<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media_has_models', function (Blueprint $table) {
            $table->string('collection_name')->nullable()->after('media_id');
            $table->boolean('responsive_images')->default(false)->after('collection_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_has_models', function (Blueprint $table) {
            $table->dropColumn(['collection_name', 'responsive_images']);
        });
    }
};
