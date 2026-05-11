<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ldap_username')) {
                $table->string('ldap_username', 150)->nullable()->after('email');
                $table->unique('ldap_username', 'users_ldap_username_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ldap_username')) {
                $table->dropUnique('users_ldap_username_unique');
                $table->dropColumn('ldap_username');
            }
        });
    }
};

