<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublishTourSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publish_tour_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_id');
            $table->unsignedInteger('user_id');

            $table->datetime('approved_at')->nullable();
            $table->datetime('denied_at')->nullable();

            $table->timestamps();

            $table->foreign('tour_id')->references('id')->on('tours');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('publish_tour_submissions');
    }
}
