<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheloaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theloais', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tentl');
            $table->unsignedBigInteger('idlt');
            $table->foreign('idlt')->references('id')->on('loaitins')->onDelete('cascade');
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
        Schema::dropIfExists('theloais');
    }
}
