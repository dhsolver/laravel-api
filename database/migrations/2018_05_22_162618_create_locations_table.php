<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');

            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city', 155)->nullable();
            $table->string('state', 155)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('zipcode', 12)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->unsignedInteger('locationable_id')->index();
            $table->string('locationable_type')->index();

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
        Schema::dropIfExists('locations');
    }
}
