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

            $table->string('title', 100)->unique();
            $table->text('description')->nullable();
            $table->string('pricing_type', 10);
            $table->string('type', 10);

            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('video_url')->nullable();

            $table->boolean('has_prize')->default(false);
            $table->string('prize_details')->nullable();
            $table->string('prize_instructions')->nullable();

            $table->string('start_message', 1000)->nullable();
            $table->string('start_video_url')->nullable();
            $table->string('end_message', 1000)->nullable();
            $table->string('end_video_url')->nullable();

            $table->unsignedInteger('start_point_id')->nullable();
            $table->unsignedInteger('end_point_id')->nullable();

            // media
            $table->unsignedInteger('intro_audio_id')->nullable();
            $table->unsignedInteger('background_audio_id')->nullable();
            $table->unsignedInteger('main_image_id')->nullable();
            $table->unsignedInteger('image1_id')->nullable();
            $table->unsignedInteger('image2_id')->nullable();
            $table->unsignedInteger('image3_id')->nullable();
            $table->unsignedInteger('trophy_image_id')->nullable();
            $table->unsignedInteger('start_image_id')->nullable();
            $table->unsignedInteger('end_image_id')->nullable();
            $table->unsignedInteger('pin_image_id')->nullable();

            $table->datetime('published_at')->nullable();
            $table->boolean('is_draft')->default(0);

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
