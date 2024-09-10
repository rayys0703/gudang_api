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
        Schema::create('serial_number', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->unsigned();
            $table->unsignedBigInteger('serial_number')->unique();
            $table->unsignedBigInteger('barangmasuk_id');
            $table->timestamps();

            $table->foreign('barangmasuk_id')->references('id')->on('barang_masuk')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_number');
    }
};
