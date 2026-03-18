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
        Schema::create('linktree_orders', function (Blueprint $table) {
            $table->id();
            // Gunakan foreignId agar otomatis menjadi UnsignedBigInteger
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Pastikan ini mereferensi ke tabel 'linktrees'
            $table->foreignId('linktree_id')->constrained('linktrees')->onDelete('cascade');
            
            $table->string('order_id')->unique();
            $table->string('plan_type');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending'); 
            $table->string('snap_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linktree_orders');
    }
};
