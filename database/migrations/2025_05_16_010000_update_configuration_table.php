<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('configuration', function (Blueprint $table) {
            $table->dropColumn(['notification', 'public_profile', 'view_statistics', 'allow_messages']);
            $table->string('configuration', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::table('configuration', function (Blueprint $table) {
            $table->dropColumn('configuracion');
            $table->string('notification', 255)->nullable();
            $table->string('public_profile', 255)->nullable();
            $table->string('view_statistics', 255)->nullable();
            $table->string('allow_messages', 255)->nullable();
        });
    }
};
