<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email')->unique();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->text('avatar')->nullable();
            $table->text('cover')->nullable();
            $table->text('bio')->nullable();
            $table->date('dob')->nullable();
            $table->string('timezone', 30)->default('UTC');
            $table->unsignedInteger('points')->default(0)->index();
            $table->ipAddress('ip')->nullable();
            $table->unsignedTinyInteger('status')->default(1)->index();

            $table->foreignId('gender_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
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
        Schema::dropIfExists('users');
    }
}
