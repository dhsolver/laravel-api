<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTableAddTourLimitColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('tour_limit')->after('remember_token')->default(0);
        });

        foreach (\App\Client::withCount('tours')->get() as $user) {
            $limit = $user->tours_count > 0 ? $user->tours_count : 3;
            $user->update(['tour_limit' => $limit]);
        }

        foreach (\App\Admin::withCount('tours')->get() as $user) {
            $limit = $user->tours_count > 0 ? $user->tours_count : 3;
            $user->update(['tour_limit' => 999]);
        }

        foreach (\App\MobileUser::all() as $user) {
            $user->update(['tour_limit' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tour_limit']);
        });
    }
}
