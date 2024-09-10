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
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->unsigned();
            //$table->string('bm_kode')->unique();
            //$table->bigInteger('serial_number')->unique();
            $table->unsignedBigInteger('barang_id');
            //$table->unsignedBigInteger('status_barang_id');
            $table->unsignedBigInteger('jumlah');
            $table->string('keterangan')->nullable();
            $table->date('tanggal');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade')->onUpdate('cascade');
            //$table->foreign('status_barang_id')->references('id')->on('status_barang')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_masuk');
    }
};
