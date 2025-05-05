<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_word_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('word_id')->constrained()->onDelete('cascade');
            $table->boolean('answered_correctly')->default(false);
            $table->integer('times_shown')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'word_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_word_history');
    }
};