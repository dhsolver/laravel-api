<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateToursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');

            $table->string('title', 100);
            $table->string('description', 2000);
            $table->string('pricing_type', 10);
            $table->string('type', 10);

            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zipcode', 12)->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();

            // media
            $table->string('intro_audio')->nullable();
            $table->string('background_audio')->nullable();

            $table->string('main_image')->nullable();
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();

            $table->string('video_url')->nullable();

            $table->string('trophy_image')->nullable();
            $table->string('price_details')->nullable();
            $table->string('price_instructions')->nullable();

            $table->unsignedInteger('start_point')->nullable();
            $table->unsignedInteger('end_point')->nullable();
            $table->string('start_message', 1000)->nullable();
            $table->string('start_media')->nullable();
            $table->string('end_message', 1000)->nullable();
            $table->string('end_media')->nullable();

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
        Schema::dropIfExists('tours');
    }
}
