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

            $table->string('title');
            $table->string('description', 2000);
            $table->string('location_type'); // map/address/gps

            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zipcode')->nullable();

            // media
            // $table->string('play_radius');
            $table->string('audio')->nullable();

            $table->string('main_image');
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();

            $table->string('video_url')->nullable();

            $table->tinyInteger('is_multiple_choice')->default(0);
            $table->string('question', 500)->nullable();
            $table->string('question_answer')->nullable();
            $table->string('question_success')->nullable();

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
