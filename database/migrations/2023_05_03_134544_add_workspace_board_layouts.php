<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('board_layouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('board_id')->index();
            $table->string('title', 128);
            $table->json('layout');
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
        \Schema::drop('board_layouts');
    }
};
