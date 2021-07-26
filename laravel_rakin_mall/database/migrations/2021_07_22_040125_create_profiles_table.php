<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prof', function (Blueprint $table) {
            $table->id();
            $table->integer('usr_id')->unsigned();
            $table->integer('posn_id')->unsigned()->nullable();
            $table->integer('dest_id')->unsigned()->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('fullname_trans')->nullable();
            $table->string('provider',20)->nullable();
            $table->string('provider_unique_id',50)->nullable();
            $table->string('gender',20)->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone',20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_anniversary')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city',50)->nullable();
            $table->string('state',50)->nullable();
            $table->string('zipcode',10)->nullable();
            $table->string('country_id',10)->nullable();
            $table->string('facebook_profile')->nullable();
            $table->string('twitter_profile')->nullable();
            $table->string('google_plus_profile')->nullable();
            $table->string('linkedin_profile')->nullable();
            $table->boolean('hide_organization')->default(0);
            $table->boolean('is_allow_email')->default(0);

            $table->timestamps();
            $table->softDeletes();
            // foreign key
            $table->foreign('usr_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('posn_id')->references('id')->on('posn')->onDelete('cascade');
            $table->foreign('dest_id')->references('id')->on('dest')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prof');
    }
}
