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
        Schema::create('command_queues', function (Blueprint $table) {
            $table->id();
            $table->string('istance_key');
            $table->string('cmd_name');
            $table->integer('ticket')->nullable();
            $table->integer('side')->nullable();
            $table->integer('magnum')->nullable();
            $table->integer('Automatism_id')->nullable();
            $table->decimal('lot', 10, 5)->nullable();
            $table->decimal('tp', 10, 5)->nullable();
            $table->decimal('sl', 10, 5)->nullable();
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
        
        Schema::dropIfExists('command_queues');
    }
};
