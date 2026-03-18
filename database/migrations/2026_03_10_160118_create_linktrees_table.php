<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('linktrees', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // Slug/ID untuk URL
            $table->string('name');
            $table->string('username');
            $table->text('bio')->nullable();
            $table->longText('avatar')->nullable(); // Base64 String
            $table->boolean('verified')->default(false);
            $table->integer('visitors')->default(0);
            
            // Kita simpan links dan socials dalam format JSON agar fleksibel
            $table->text('links_data')->nullable(); 
            $table->text('socials_data')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('linktrees');
    }
};
