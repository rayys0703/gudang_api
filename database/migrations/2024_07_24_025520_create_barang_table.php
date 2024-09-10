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
        Schema::create('barang', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->unsigned();
            //$table->bigInteger('serial_number')->unique();
            $table->unsignedBigInteger('jenis_barang_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('nama');
            //$table->bigInteger('jumlah')->default(0);
            //$table->enum('status', ['Rusak', 'Baik'])->default('Baik');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('supplier')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('jenis_barang_id')->references('id')->on('jenis_barang')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
