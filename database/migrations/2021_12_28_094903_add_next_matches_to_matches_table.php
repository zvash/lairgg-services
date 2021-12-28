<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNextMatchesToMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->unsignedBigInteger('winner_next_match_id')->nullable()->after('winner_team_id');
            $table->unsignedBigInteger('loser_next_match_id')->nullable()->after('winner_next_match_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('winner_next_match_id');
            $table->dropColumn('loser_next_match_id');
        });
    }
}
