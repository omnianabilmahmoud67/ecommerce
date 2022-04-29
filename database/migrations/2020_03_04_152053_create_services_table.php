<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->longText('short_desc_ar')->nullable();
            $table->longText('short_desc_en')->nullable();
            $table->longText('desc_ar')->nullable();
            $table->longText('desc_en')->nullable();
            $table->integer('amount')->nullable();
            $table->double('price')->nullable();
            $table->string('image')->nullable();

            $table->integer('section_id')->unsigned();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->integer('sub_section_id')->unsigned();
            $table->foreign('sub_section_id')->references('id')->on('sub_sections')->onDelete('cascade');
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
        Schema::dropIfExists('services');
    }
}
