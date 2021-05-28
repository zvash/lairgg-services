<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invited_by')->constrained('users');
            $table->foreignId('organization_id')->nullable();
            $table->morphs('invite_aware');
            $table->string('email')->index();
            $table->boolean('accepted')->nullable()->default(null);
            $table->string('token')->nullable()->default(null);
            $table->timestamps();

            $table->unique(['invite_aware_type', 'invited_by', 'invite_aware_id', 'email'], 'invitation_unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitations');
    }
}
