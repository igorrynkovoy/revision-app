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
        \Schema::create('tool_address_deep_neighbors_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('board_id');
            $table->unsignedInteger('job_id')->nullable()->default(null);
            $table->unsignedInteger('depth_sync_id');
            $table->json('settings');
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
        //
    }
};
