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
        Schema::create('istance_closed_postions', function (Blueprint $table) {
            $table->id();
            $table->string('istance_key');
            $table->string('ticket');
            $table->string('pair');
            $table->decimal('profit', 10, 2); // Assuming profit is a decimal value
            $table->decimal('open_price', 10, 5); // Assuming open price is a decimal value
            $table->decimal('take_profit', 10, 5); // Assuming take profit is a decimal value
            $table->decimal('stop_loss', 10, 5); // Assuming stop loss is a decimal value
            $table->string('side'); // Assuming side is a string value like 'buy' or 'sell'
            $table->decimal('lot_size', 10, 5); // Assuming lot size is a decimal value
            $table->bigInteger('magic_number'); // Assuming magic number is an integer value
            $table->text('comment')->nullable(); // Assuming comment is a text field and can be null
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

        Schema::dropIfExists('istance_closed_postions');
    }
};
