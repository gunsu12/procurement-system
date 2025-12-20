<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChecklistToProcurementItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procurement_items', function (Blueprint $table) {
            $table->boolean('is_checked')->default(false)->after('budget_info');
            $table->timestamp('checked_at')->nullable()->after('is_checked');
            $table->foreignId('checked_by')->nullable()->constrained('users')->onDelete('set null')->after('checked_at');
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
            $table->dropForeign(['checked_by']);
            $table->dropColumn(['is_checked', 'checked_at', 'checked_by']);
        });
    }
}
