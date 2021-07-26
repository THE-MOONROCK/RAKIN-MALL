<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dvctk', function (Blueprint $table) {
            $table->id();
            $table->integer('usr_id')->unsigned();
            $table->string('device_name', 255);
            $table->string('device_token', 255);
            $table->string('app_version_number', 255)->nullable();
            $table->string('device_platform', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();
            // foreign key
            $table->foreign('usr_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dvctk');
    }
}
