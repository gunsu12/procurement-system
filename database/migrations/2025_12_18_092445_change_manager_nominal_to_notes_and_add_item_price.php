<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeManagerNominalToNotesAndAddItemPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->renameColumn('manager_nominal', 'notes');
        });

        Schema::table('procurement_items', function (Blueprint $table) {
            $table->decimal('estimated_price', 15, 2)->default(0)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->renameColumn('notes', 'manager_nominal');
        });

        Schema::table('procurement_items', function (Blueprint $table) {
            $table->dropColumn('estimated_price');
        });
    }
}
