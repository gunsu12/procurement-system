<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSsoFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sso_id')->nullable()->unique()->after('id');
            $table->string('employee_id')->nullable()->index()->after('sso_id');
            $table->string('department')->nullable()->after('email');
            $table->string('position')->nullable()->after('department');
            $table->string('avatar_url')->nullable()->after('position');
            $table->timestamp('last_sso_sync')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sso_id',
                'employee_id',
                'department',
                'position',
                'avatar_url',
                'last_sso_sync'
            ]);
        });
    }
}
