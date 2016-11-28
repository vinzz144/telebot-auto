<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_tb', function (Blueprint $table) {
            $table->increments('id')->primary();
            $table->string('update_id');
            $table->string('message_id');
            $table->integer('replied');
            $table->string('chat_id',255);
            $table->string('chat_type',255);
            $table->string('from_id',255);
            $table->string('first_name',255);
            $table->string('last_name',255);
            $table->text('text');
            $table->text('data');
            $table->text('response_data');
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
        Schema::dropIfExists('message_tb');
    }
}
