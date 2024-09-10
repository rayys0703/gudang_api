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
        Schema::create('detail_barang_masuk', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->unsigned();
            $table->unsignedBigInteger('barangmasuk_id');
            $table->unsignedBigInteger('serial_number_id');
            $table->unsignedBigInteger('status_barang_id');
            $table->string('kelengkapan')->nullable();
            $table->timestamps();

            $table->foreign('serial_number_id')->references('id')->on('serial_number')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('barangmasuk_id')->references('id')->on('barang_masuk')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('status_barang_id')->references('id')->on('status_barang')->onDelete('cascade')->onUpdate('cascade');
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
