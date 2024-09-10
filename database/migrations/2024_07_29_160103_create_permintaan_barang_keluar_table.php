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
        Schema::create('permintaan_barang_keluar', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->unsigned();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('keperluan_id');
            $table->unsignedBigInteger('jumlah');
            $table->string('keterangan')->nullable();
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir')->nullable();
            $table->enum('status', ['Belum Disetujui', 'Disetujui', 'Ditolak'])->default('Belum Disetujui');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('keperluan_id')->references('id')->on('keperluan')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_barang_keluar');
    }
};
