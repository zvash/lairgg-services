<?php

use App\Enums\TournamentStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_types', function (Blueprint $table) {
            $table->id();
            $table->string('title');

            $table->enum('stage', [
                TournamentStage::Dual,
                TournamentStage::FFA,
            ])->index();

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
        Schema::dropIfExists('tournament_types');
    }
}
