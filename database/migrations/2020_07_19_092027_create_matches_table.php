<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('round')->nullable()->index();
            $table->unsignedInteger('group')->nullable()->index();
            $table->unsignedInteger('play_count')->default(1)->index();
            $table->boolean('is_forfeit')->default(false)->index();

            $table->foreignId('tournament_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('winner_team_id')
                ->nullable()
                ->constrained('teams')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamp('started_at')->nullable();

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
        Schema::dropIfExists('matches');
    }
}
