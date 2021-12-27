<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartedAtAndIsForfeitToPlaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plays', function (Blueprint $table) {
            $table->boolean('is_forfeit')->default(false)->after('edited_by');
            $table->timestamp('started_at')->nullable()->after('is_forfeit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plays', function (Blueprint $table) {
            $table->dropColumn('is_forfeit');
            $table->dropColumn('started_at');
        });
    }
}
