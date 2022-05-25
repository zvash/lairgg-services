<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePushNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('type');
            $table->string('title')->nullable()->default(null);
            $table->text('body');
            $table->text('image')->nullable();
            $table->unsignedBigInteger('resource_id')->nullable()->default(null);
            $table->json('payload')->nullable()->default(null);
            $table->timestamp('read_at')->nullable()->default(null);
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
        Schema::dropIfExists('push_notifications');
    }
}
