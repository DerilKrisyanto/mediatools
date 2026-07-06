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
        Schema::create('memo_pengirimans', function (Blueprint $table) {
            $table->id();

            // Relasi ke user yang menginput memo.
            // Digunakan untuk membatasi data yang tampil hanya milik user tsb.
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('nomor_memo')->unique();
            $table->date('tanggal_memo');

            // Bagian: Telah Terima Dari
            $table->string('diterima_dari');
            $table->string('telepon_dari')->nullable();
            $table->text('berupa');

            // Bagian: Untuk Dikirimkan Ke
            $table->string('tujuan_contact_person');
            $table->text('tujuan_alamat');
            $table->string('tujuan_telepon')->nullable();
            $table->string('pengiriman_hari_tanggal')->nullable();
            $table->decimal('biaya_kirim', 12, 2)->nullable();

            // Bagian: Instalasi
            $table->boolean('instalasi')->default(false);
            $table->string('instalasi_hari_tanggal')->nullable();
            $table->decimal('biaya_instalasi', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_pengirimans');
    }
};