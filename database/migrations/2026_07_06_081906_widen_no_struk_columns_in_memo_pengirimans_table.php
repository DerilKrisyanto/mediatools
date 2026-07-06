<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->string('no_struk', 500)->change();
            $table->string('no_struk_instalasi', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('memo_pengirimans', function (Blueprint $table) {
            $table->string('no_struk', 100)->change();
            $table->string('no_struk_instalasi', 100)->nullable()->change();
        });
    }
};