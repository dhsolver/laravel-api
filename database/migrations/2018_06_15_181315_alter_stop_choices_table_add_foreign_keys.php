<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterStopChoicesTableAddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stop_choices', function (Blueprint $table) {
            $table->foreign('tour_stop_id')->references('id')->on('tour_stops');
            $table->foreign('next_stop_id')->references('id')->on('tour_stops');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() != 'sqlite') {
            Schema::table('stop_choices', function (Blueprint $table) {
                $table->dropForeign(['tour_stop_id']);
                $table->dropForeign(['next_stop_id']);
            });
        }
    }
}
