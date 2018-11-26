<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStopStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stop_stats', function (Blueprint $table) {
            $table->increments('id');
            // $table->unsignedInteger('tour_id');
            $table->unsignedInteger('stop_id');
            $table->string('yyyymmdd', 8)->index();
            $table->integer('visits');
            $table->integer('time_spent');
            $table->integer('actions');
            $table->boolean('final')->default(false);
            $table->timestamps();

            $table->foreign('stop_id')->references('id')->on('tour_stops')->onDelete('cascade');
            $table->unique(['stop_id', 'yyyymmdd']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stop_stats');
    }
}
