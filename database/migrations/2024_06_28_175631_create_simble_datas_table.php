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
        Schema::create('simble_datas', function (Blueprint $table) {
            $table->id();
            $table->string('istance_key');
            $table->string('simble_name');
            $table->decimal('current_ask', 10, 5);
            $table->decimal('current_bid', 10, 5);
            $table->decimal('current_spread', 10, 5);
            $table->boolean('trading_is_active');
            $table->string('time_frame');
            $table->decimal('open', 10, 5);
            $table->decimal('current_high', 10, 5);
            $table->decimal('current_low', 10, 5);
            $table->json('past_candle_json')->nullable();
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
        
        Schema::dropIfExists('simble_datas');
    }
};
