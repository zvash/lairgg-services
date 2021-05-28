<?php

use App\Enums\ParticipantAcceptanceState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusFieldToParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->enum('status', [
                ParticipantAcceptanceState::PENDING,
                ParticipantAcceptanceState::ACCEPTED,
                ParticipantAcceptanceState::REJECTED,
                ParticipantAcceptanceState::RESERVED,
            ])->after('participantable_id')
                ->default(ParticipantAcceptanceState::PENDING);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
