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
        Schema::create('detail_permintaan_bk', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->unsigned();
            $table->unsignedBigInteger('permintaan_barang_keluar_id');
            $table->unsignedBigInteger('barang_id'); // serial_number_id
            $table->unsignedBigInteger('jumlah');
            $table->string('keterangan')->nullable();
            //$table->timestamps();

            $table->foreign('permintaan_barang_keluar_id')->references('id')->on('permintaan_barang_keluar')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_permintaan_bk');
    }
};
