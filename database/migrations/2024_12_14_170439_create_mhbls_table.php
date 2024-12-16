<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMhblsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mhbls', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('consignee_id');
            $table->unsignedBigInteger('shipper_id');
            $table->string('cargo_type');
            $table->unsignedBigInteger('warehouse_id');
            $table->double('grand_volume', 10, 2)->nullable();
            $table->double('grand_weight', 10, 2)->nullable();
            $table->double('grand_total', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mhbls');
    }
}
