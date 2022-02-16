<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id');
            $table->foreignId('team_id');
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();

            $table->unique(['tournament_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_balances');
    }
}
