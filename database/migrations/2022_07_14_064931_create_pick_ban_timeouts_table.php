<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickBanTimeoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pick_ban_timeouts', function (Blueprint $table) {
            $table->id();
            $table->string('lobby_name')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('action_type');
            $table->unsignedSmallInteger('current_step');
            $table->unsignedBigInteger('deadline');
            $table->json('arguments')->nullable()->default(null);
            $table->boolean('manually_selected')->default(false);
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
        Schema::dropIfExists('pick_ban_timeouts');
    }
}
