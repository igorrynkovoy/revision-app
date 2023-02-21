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
        Schema::create('depth_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('blockchain', 32);
            $table->string('address', 128);
            $table->unsignedInteger('root_sync_id')->nullable();
            $table->unsignedInteger('parent_sync_id')->nullable();
            $table->string('direction', 16)->default('both');
            $table->unsignedInteger('child_addresses')->default(0);
            $table->unsignedInteger('limit_addresses')->default(10);
            $table->unsignedInteger('limit_transactions')->default(100);
            $table->unsignedTinyInteger('max_depth');
            $table->unsignedTinyInteger('current_depth');
            $table->unsignedTinyInteger('address_synced')->default(0);
            $table->unsignedTinyInteger('processed')->default(0);
            $table->dateTime('processed_at')->nullable();
            $table->string('processed_code', 32);

            $table->timestamps();

            $table->unique(['root_sync_id', 'address'], 'root_sync_addresses');
            $table->index(['blockchain', 'address'], 'blockchain_addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('depth_syncs');
    }
};
