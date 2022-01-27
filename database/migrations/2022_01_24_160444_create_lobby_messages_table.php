<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLobbyMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lobby_messages', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->foreignId('lobby_id');
            $table->foreignId('user_id');
            $table->string('lobby_name')->index();
            $table->unsignedBigInteger('sequence');
            $table->string('type');
            $table->timestamp('sent_at');
            $table->json('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lobby_messages');
    }
}
