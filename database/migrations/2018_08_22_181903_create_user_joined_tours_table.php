<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserJoinedToursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('purchasable_id')->index();
            $table->string('purchasable_type')->index();
            $table->unsignedInteger('transactionable_id')->index();
            $table->string('transactionable_type')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('apple_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->longText('receipt_data');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('google_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('user_joined_tours', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('tour_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('tour_id')->references('id')->on('tours');
            $table->unique(['user_id', 'tour_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_joined_tours');
        Schema::dropIfExists('google_transactions');
        Schema::dropIfExists('apple_transactions');
        Schema::dropIfExists('user_transactions');
    }
}
