<?php

use App\Enums\ParticipantAcceptanceState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyStatusColumnInParticipantsTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE  `participants`  MODIFY COLUMN `status` ENUM('" .
            ParticipantAcceptanceState::PENDING . "', '" .
            ParticipantAcceptanceState::ACCEPTED . "', '" .
            ParticipantAcceptanceState::REJECTED . "', '" .
            ParticipantAcceptanceState::RESERVED . "', '" .
            ParticipantAcceptanceState::ACCEPTED_NOT_READY . "', '" .
            ParticipantAcceptanceState::DISQUALIFIED . "') NOT NULL DEFAULT '" .
            ParticipantAcceptanceState::PENDING . "'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE  `participants`  MODIFY COLUMN `status` ENUM('" .
            ParticipantAcceptanceState::PENDING . "', '" .
            ParticipantAcceptanceState::ACCEPTED . "', '" .
            ParticipantAcceptanceState::REJECTED . "', '" .
            ParticipantAcceptanceState::RESERVED . "', '" .
            ParticipantAcceptanceState::ACCEPTED_NOT_READY . "') NOT NULL DEFAULT '" .
            ParticipantAcceptanceState::PENDING . "'");
    }
}
