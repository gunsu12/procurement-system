<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsRejectedToProcurementItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procurement_items', function (Blueprint $table) {
            $table->boolean('is_rejected')->default(false);
            $table->text('rejection_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('procurement_items', function (Blueprint $table) {
            $table->dropColumn(['is_rejected', 'rejection_note']);
        });
    }
}
