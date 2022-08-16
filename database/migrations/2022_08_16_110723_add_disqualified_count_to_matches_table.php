<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisqualifiedCountToMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->smallInteger('disqualified_count')->after('coin_toss_winner_id')->default(0);
            $table->bigInteger('winner_team_id')->nullable()->default(null)->change();
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
            $table->dropColumn('disqualified_count');
            $table->unsignedBigInteger('winner_team_id')->nullable()->default(null)->change();
        });
    }
}
