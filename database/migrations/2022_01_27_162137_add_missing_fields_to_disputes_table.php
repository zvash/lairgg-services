<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropForeign(['play_id']);
            $table->dropColumn('play_id');
            $table->foreignId('match_id')->after('id');
            $table->foreignId('lobby_message_id')->after('match_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropForeign(['match_id']);
            $table->dropColumn('match_id');
            $table->dropForeign(['lobby_message_id']);
            $table->dropColumn('lobby_message_id');
            $table->foreignId('play_id')->after('id');
        });
    }
}
