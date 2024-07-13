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
        Schema::create('istance_open_positions', function (Blueprint $table) {
            $table->id();
            $table->string('istance_key');
            $table->integer('ticket')->nullable();
            $table->string('pair')->nullable();
            $table->double('profit')->nullable();
            $table->double('open_price')->nullable();
            $table->double('take_profit')->nullable();
            $table->double('stop_loss')->nullable();
            $table->integer('side')->nullable();
            $table->double('lot_size')->nullable();
            $table->integer('magic_number')->nullable();
            $table->boolean('pending_order')->nullable();
            $table->string('comment')->nullable();
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
        Schema::dropIfExists('istance_open_positions');
    }
};
