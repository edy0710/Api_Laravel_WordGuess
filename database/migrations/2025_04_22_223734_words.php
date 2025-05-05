<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('word');
            $table->text('correct_meaning');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('words');
    }
};

