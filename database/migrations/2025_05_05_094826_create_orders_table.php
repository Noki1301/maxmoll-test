<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('status'); // "active", "completed", "canceled"
        
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
