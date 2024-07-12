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
            $table->integer('ticket');
            $table->string('pair');
            $table->double('profit');
            $table->double('open_price');
            $table->double('take_profit');
            $table->double('stop_loss');
            $table->integer('side');
            $table->double('lot_size');
            $table->integer('magic_number');
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
