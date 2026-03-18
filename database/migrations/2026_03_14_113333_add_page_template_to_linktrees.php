<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('linktrees', function (Blueprint $table) {
            $table->string('page_template')
                  ->default('dark')
                  ->after('socials_data');
        });
    }

    public function down(): void
    {
        Schema::table('linktrees', function (Blueprint $table) {
            $table->dropColumn('page_template');
        });
    }
};