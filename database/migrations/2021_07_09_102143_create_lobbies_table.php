<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLobbiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->morphs('lobby_aware');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        $lobbyRepository = new \App\Repositories\LobbyRepository();
        $tournaments = \App\Tournament::all();
        foreach ($tournaments as $tournament) {
            $lobbyRepository->createBy($tournament);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lobbies');
    }
}
