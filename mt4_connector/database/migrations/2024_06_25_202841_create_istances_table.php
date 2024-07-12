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
        Schema::create('istances', function (Blueprint $table) {
            $table->id();
            $table->string('license_name');
            $table->string('license_key', 64)->unique();
            $table->boolean('status')->nullable();
            $table->string('version')->nullable();
            $table->dateTime('last_contact')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('istances');
    }
};
