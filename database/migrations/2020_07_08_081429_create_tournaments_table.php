<?php

use App\Enums\TournamentStructure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('bio')->nullable();
            $table->text('rules')->nullable();
            $table->text('image');
            $table->text('cover')->nullable();
            $table->string('timezone', 30)->default('UTC');
            $table->unsignedInteger('max_teams');
            $table->unsignedInteger('reserve_teams');
            $table->unsignedInteger('players');
            $table->unsignedInteger('check_in_period')->default(10);
            $table->float('entry_fee')->default(0);
            $table->boolean('unlisted')->default(false)->index();
            $table->boolean('invite_only')->default(true)->index();
            $table->unsignedTinyInteger('status')->default(1)->index();

            $table->enum('structure', [
                TournamentStructure::SIX,
                TournamentStructure::FIVE,
                TournamentStructure::FOUR,
                TournamentStructure::THREE,
                TournamentStructure::TWO,
                TournamentStructure::ONE,
                TournamentStructure::OTHER,
            ]);

            $table->unsignedInteger('match_check_in_period')->default(10);
            $table->unsignedInteger('match_play_count')->default(3);
            $table->boolean('match_randomize_map')->default(true);
            $table->boolean('match_third_rank')->default(false);

            $table->unsignedInteger('league_win_score')->nullable();
            $table->unsignedInteger('league_tie_score')->nullable();
            $table->unsignedInteger('league_lose_score')->nullable();
            $table->unsignedInteger('league_match_up_count')->nullable();

            $table->foreignId('region_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('tournament_type_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('game_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('organization_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments');
    }
}
