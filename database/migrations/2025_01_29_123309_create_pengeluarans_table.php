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
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id("id_pengeluaran");
            $table->integer("pengeluaran");
            $table->timestamps();
            $table->date('tanggal');
            $table->unsignedBigInteger('id_kategori_pengeluaran'); // Pastikan tipe data sesuai

            // Menambahkan foreign key
            $table->foreign('id_kategori_pengeluaran')
                ->references('id_kategori_pengeluaran')
                ->on('kategori_pengeluaran')
                ->onDelete('cascade');

            $table->unsignedBigInteger('id_user'); // Pastikan tipe data sesuai

            // Menambahkan foreign key
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
