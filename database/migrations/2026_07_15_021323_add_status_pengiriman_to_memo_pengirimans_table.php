<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            // true = Dikirim (default), false = Pending
            $table->boolean('status_pengiriman')->default(true)->after('instalasi');
        });
    }

    public function down(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->dropColumn('status_pengiriman');
        });
    }
};