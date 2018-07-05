<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourStopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tour_stops', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('tour_id');
            $table->unsignedSmallInteger('order');

            $table->string('title', 255);
            $table->text('description')->nullable();

            // media
            $table->unsignedInteger('intro_audio_id')->nullable();
            $table->unsignedInteger('background_audio_id')->nullable();
            $table->decimal('play_radius', 13, 2)->default(0);

            $table->unsignedInteger('main_image_id')->nullable();
            $table->unsignedInteger('image1_id')->nullable();
            $table->unsignedInteger('image2_id')->nullable();
            $table->unsignedInteger('image3_id')->nullable();

            $table->string('video_url')->nullable();

            $table->tinyInteger('is_multiple_choice')->default(0);
            $table->string('question', 500)->nullable();
            $table->string('question_answer', 500)->nullable();
            $table->string('question_success', 500)->nullable();
            $table->unsignedInteger('next_stop_id')->nullable();

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
        Schema::dropIfExists('tour_stops');
    }
}
