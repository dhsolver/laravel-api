<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserScoreCardStopsTableAddColumnSkippedQuestionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_score_card_stops', function (Blueprint $table) {
            $table->boolean('skipped_question')->default(false)->after('stop_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_score_card_stops', function (Blueprint $table) {
            $table->dropColumn(['skipped_question']);
        });
    }
}
