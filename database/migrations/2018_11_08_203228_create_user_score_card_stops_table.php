<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserScoreCardStopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_score_card_stops', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('score_card_id');
            $table->unsignedInteger('stop_id');
            $table->timestamp('visited_at');

            $table->unique(['score_card_id', 'stop_id']);
            $table->foreign('score_card_id')->references('id')->on('user_score_cards')->onDelete('cascade');
            $table->foreign('stop_id')->references('id')->on('tour_stops')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_score_card_stops');
    }
}
