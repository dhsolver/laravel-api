<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;
use Illuminate\Support\Str;

class AlterUsersTableAddEmailConfirmationFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('email_confirmation_token', 64)->nullable()->after('subscribe_override')->unique();
            $table->timestamp('email_confirmed_at')->nullable()->before('created_at');
        });

        foreach (User::all() as $user) {
            $user->email_confirmation_token = Str::random(64);
            $user->save();
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
            $table->dropColumn(['email_confirmation_token', 'email_confirmed_at']);
        });
    }
}
