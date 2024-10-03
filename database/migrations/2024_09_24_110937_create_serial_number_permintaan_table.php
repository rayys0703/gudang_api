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
        Schema::create('serial_number_permintaan', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->unsigned();
            $table->unsignedBigInteger('detail_permintaan_bk_id');
            $table->unsignedBigInteger('serial_number_id')->nullable();
            //$table->timestamps();

            $table->foreign('detail_permintaan_bk_id')->references('id')->on('detail_permintaan_bk')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('serial_number_id')->references('id')->on('serial_number')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_number_permintaan');
    }
};
