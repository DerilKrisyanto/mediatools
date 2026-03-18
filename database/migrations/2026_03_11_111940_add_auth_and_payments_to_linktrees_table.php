<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('linktrees', function (Blueprint $table) {
            // Menghubungkan ke tabel users (karena user harus login)
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Kolom Status Bisnis
            $table->boolean('is_active')->default(false)->after('verified'); // Aktif setelah bayar
            $table->string('plan_type')->nullable()->after('is_active'); // starter, best_value, business
            $table->timestamp('expired_at')->nullable()->after('plan_type'); // Masa berlaku
        });
    }

    public function down()
    {
        Schema::table('linktrees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_active', 'plan_type', 'expired_at']);
        });
    }
};