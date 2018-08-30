<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_udid')->unique();
            $table->enum('os', ['ios', 'android', 'windows', 'mac', 'linux', 'other'])->default('other')->index();
            $table->enum('type', ['phone', 'tablet', 'web', 'mobile_web', 'unknown'])->default('unknown')->index();
            $table->string('user_agent', 1000)->nullable();

            $table->timestamps();
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->uuid('device_id');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('device_id')->references('id')->on('devices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('devices');
    }
}
