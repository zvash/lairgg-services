<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notification_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->default(null);
            $table->text('passport_token')->nullable()->default(null);
            $table->string('platform');
            $table->text('token');
            $table->timestamp('registered_at');
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
        Schema::dropIfExists('user_notification_tokens');
    }
}
