<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->text('nama_sales')->nullable()->after('customer_service');
        });
    }

    public function down(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->dropColumn('nama_sales');
        });
    }
};