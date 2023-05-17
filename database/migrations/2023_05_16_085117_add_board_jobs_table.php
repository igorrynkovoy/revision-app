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
        Schema::create('board_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('board_id');
            $table->morphs('jobable', 'jobable');
            $table->boolean('finished')->default(false)->index();
            $table->dateTime('finished_at')->nullable()->default(null);
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
        Schema::dropIfExists('board_jobs');
    }
};
