<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('qrs', function (Blueprint $name) {
            $name->id();
            $name->foreignId('user_id')->constrained()->onDelete('cascade');
            $name->text('content'); // URL atau Teks
            $name->json('settings'); // Warna, bentuk, logo
            $name->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qrs');
    }
};
