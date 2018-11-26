<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterToursTableAddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('start_point_id')->references('id')->on('tour_stops');
            $table->foreign('end_point_id')->references('id')->on('tour_stops');
            $table->foreign('intro_audio_id')->references('id')->on('media');
            $table->foreign('background_audio_id')->references('id')->on('media');
            $table->foreign('main_image_id')->references('id')->on('media');
            $table->foreign('image1_id')->references('id')->on('media');
            $table->foreign('image2_id')->references('id')->on('media');
            $table->foreign('image3_id')->references('id')->on('media');
            $table->foreign('trophy_image_id')->references('id')->on('media');
            $table->foreign('start_image_id')->references('id')->on('media');
            $table->foreign('end_image_id')->references('id')->on('media');
            $table->foreign('pin_image_id')->references('id')->on('media');
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
            Schema::table('tours', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['start_point_id']);
                $table->dropForeign(['end_point_id']);
                $table->dropForeign(['intro_audio_id']);
                $table->dropForeign(['background_audio_id']);
                $table->dropForeign(['main_image_id']);
                $table->dropForeign(['image1_id']);
                $table->dropForeign(['image2_id']);
                $table->dropForeign(['image3_id']);
                $table->dropForeign(['trophy_image_id']);
                $table->dropForeign(['start_image_id']);
                $table->dropForeign(['end_image_id']);
                $table->dropForeign(['pin_image_id']);
            });
        }
    }
}
