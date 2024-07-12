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
        Schema::create('account_datas', function (Blueprint $table) {
            $table->id();
            $table->string('istance_key');
            $table->decimal('profit', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->integer('account_number');
            $table->string('broker_name');
            $table->string('account_name');
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
        
        Schema::dropIfExists('account_datas');
    }
};
