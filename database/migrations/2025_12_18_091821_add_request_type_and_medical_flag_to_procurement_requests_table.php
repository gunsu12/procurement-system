<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestTypeAndMedicalFlagToProcurementRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->enum('request_type', ['aset', 'nonaset'])->default('nonaset')->after('manager_nominal');
            $table->boolean('is_medical')->default(false)->after('request_type');
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
            $table->dropColumn(['request_type', 'is_medical']);
        });
    }
}
