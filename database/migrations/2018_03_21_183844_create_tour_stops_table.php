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
            $table->unsignedTinyInteger('order');

            $table->string('title', 255);
            $table->string('description', 2000);

            // location
            $table->string('location_type', 10); // map/address/gps

            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zipcode', 12)->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // media
            // $table->string('play_radius');
            $table->string('audio')->nullable();

            $table->string('main_image')->nullable();
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

            // $table->unique(['tour_id', 'order']);
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
