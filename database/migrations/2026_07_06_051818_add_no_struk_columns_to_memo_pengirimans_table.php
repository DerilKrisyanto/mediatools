<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->string('no_struk')->nullable()->after('diterima_dari');
            $table->string('no_struk_instalasi')->nullable()->after('instalasi_hari_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->dropColumn(['no_struk', 'no_struk_instalasi']);
        });
    }
};