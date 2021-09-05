<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_costs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_server');
            $table->string('lokasi_server');
            $table->string('tipe_server');
            $table->string('pic_team_server');
            $table->double('total_cost')->nullable();
            $table->bigInteger('edited_by_id')->unsigned()->nullable();
            $table->bigInteger('cost_id')->unsigned()->nullable();
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
        Schema::dropIfExists('history_costs');
    }
}
