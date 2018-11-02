<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->unsignedInteger('points')->default(0)->index();
            $table->unsignedInteger('tours_completed')->default(0);
            $table->unsignedInteger('stops_visited')->default(0);
            $table->unsignedInteger('trophies')->default(0);

            $table->foreign('user_id')->references('id')->on('users');
        });

        foreach (\App\User::with('stats')->get() as $user) {
            if (empty($user->stats)) {
                \App\UserStats::create([
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_stats');
    }
}
