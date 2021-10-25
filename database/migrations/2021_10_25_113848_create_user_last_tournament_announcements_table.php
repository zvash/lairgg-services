<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLastTournamentAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_last_tournament_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('tournament_id');
            $table->foreignId('tournament_announcement_id');
            $table->timestamps();

            $table->unique(['user_id', 'tournament_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_last_tournament_announcements');
    }
}
