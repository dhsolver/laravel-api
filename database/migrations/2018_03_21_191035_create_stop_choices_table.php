<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStopChoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stop_choices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_stop_id');
            $table->unsignedTinyInteger('order');
            $table->string('answer');
            $table->unsignedInteger('next_stop_id')->nullable();

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
        Schema::dropIfExists('stop_choices');
    }
}
