<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGameIdFieldToCoinTossReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_toss_reasons', function (Blueprint $table) {
            $table->foreignId('game_id')->default(1)->afte('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_toss_reasons', function (Blueprint $table) {
            $table->dropColumn('game_id');
        });
    }
}
