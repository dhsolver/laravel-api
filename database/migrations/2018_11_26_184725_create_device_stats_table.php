<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tour_id');
            $table->string('yyyymmdd', 8)->index();
            $table->enum('os', ['ios', 'android', 'windows', 'mac', 'linux', 'other'])->default('other')->index();
            $table->enum('device_type', ['phone', 'tablet', 'web', 'mobile_web', 'unknown'])->default('unknown')->index();
            $table->integer('downloads');
            $table->integer('actions');
            $table->integer('visitors');
            $table->boolean('final')->default(false);
            $table->timestamps();

            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
            $table->unique(['tour_id', 'yyyymmdd', 'os', 'device_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_stats');
    }
}
