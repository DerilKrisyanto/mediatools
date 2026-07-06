<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->string('customer_service', 150)->nullable()->after('tujuan_telepon');
        });
    }

    public function down(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->dropColumn('customer_service');
        });
    }
};