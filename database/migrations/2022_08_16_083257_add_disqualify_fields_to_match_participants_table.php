<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisqualifyFieldsToMatchParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('match_participants', function (Blueprint $table) {
            $table->timestamp('disqualify_deadline')->after('match_date')->nullable()->default(null)->index();
            $table->timestamp('disqualified_at')->after('ready_at')->nullable()->default(null)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('match_participants', function (Blueprint $table) {
            $table->dropColumn('disqualify_deadline');
            $table->dropColumn('disqualified_at');
        });
    }
}
