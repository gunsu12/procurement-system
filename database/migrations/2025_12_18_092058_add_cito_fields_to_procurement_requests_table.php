<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCitoFieldsToProcurementRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->boolean('is_cito')->default(false)->after('is_medical');
            $table->text('cito_reason')->nullable()->after('is_cito');
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
            $table->dropColumn(['is_cito', 'cito_reason']);
        });
    }
}
