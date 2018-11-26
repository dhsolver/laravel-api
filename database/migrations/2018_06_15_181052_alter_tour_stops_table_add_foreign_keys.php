<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTourStopsTableAddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->foreign('tour_id')->references('id')->on('tours');
            $table->foreign('intro_audio_id')->references('id')->on('media');
            $table->foreign('background_audio_id')->references('id')->on('media');
            $table->foreign('main_image_id')->references('id')->on('media');
            $table->foreign('image1_id')->references('id')->on('media');
            $table->foreign('image2_id')->references('id')->on('media');
            $table->foreign('image3_id')->references('id')->on('media');
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
            Schema::table('tour_stops', function (Blueprint $table) {
                $table->dropForeign(['tour_id']);
                $table->dropForeign(['intro_audio_id']);
                $table->dropForeign(['background_audio_id']);
                $table->dropForeign(['main_image_id']);
                $table->dropForeign(['image1_id']);
                $table->dropForeign(['image2_id']);
                $table->dropForeign(['image3_id']);
                $table->dropForeign(['next_stop_id']);
            });
        }
    }
}
