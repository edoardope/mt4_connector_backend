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
        Schema::create('istance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('istance_key');
            $table->string('market_refresh_rate');
            $table->string('status_refresh_rate');
            $table->timestamps();

            $table->foreign('istance_key')->references('license_key')->on('istances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->dropForeign(['istance_key']);
        $table->dropColumn('istance_key');

        Schema::dropIfExists('istance_settings');
    }
};
