<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStopRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stop_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_id');
            $table->unsignedInteger('stop_id');
            $table->unsignedInteger('next_stop_id');
            $table->unsignedSmallInteger('order');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->foreign('tour_id')->references('id')->on('tours');
            $table->foreign('stop_id')->references('id')->on('tour_stops');
            $table->foreign('next_stop_id')->references('id')->on('tour_stops');

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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('stop_routes');
        Schema::enableForeignKeyConstraints();
    }
}
