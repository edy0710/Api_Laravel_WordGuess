<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('word_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // 'query' o 'answer'
            $table->boolean('is_correct')->nullable(); // Solo para eventos de respuesta
            $table->string('user_ip')->nullable();
            $table->timestamps(); // created_at será nuestra fecha de registro
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('word_events');
    }
};
