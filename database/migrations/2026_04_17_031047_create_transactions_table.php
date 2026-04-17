<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
 
            // 'income' = pemasukan | 'expense' = pengeluaran
            $table->enum('type', ['income', 'expense']);
 
            $table->string('name');                          // Nama pemasukan/pengeluaran
            $table->decimal('quantity', 12, 2)->default(1); // Jumlah item
            $table->decimal('price_per_item', 15, 2);       // Harga per item
            $table->decimal('total_amount', 15, 2);         // quantity × price_per_item
            $table->date('transaction_date');                // Tanggal transaksi
            $table->text('notes')->nullable();               // Catatan
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
