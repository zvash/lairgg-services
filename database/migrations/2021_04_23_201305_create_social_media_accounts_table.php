<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialMediaAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('provider');
            $table->string('provider_user_id');
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_media_accounts');
    }
}
