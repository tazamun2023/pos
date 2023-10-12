<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zatka_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('trx_id')->unsigned();
            $table->string('info');
            $table->integer('status_code');
            $table->timestamps();
            $table->foreign('trx_id')->on('transactions')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zatka_infos');
    }
};
