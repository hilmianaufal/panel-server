<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deploy_projects', function (Blueprint $table) {
            $table->boolean('auto_database')->default(false);
            $table->string('db_name')->nullable();
            $table->string('db_username')->nullable();
            $table->string('db_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deploy_projects', function (Blueprint $table) {
            //
        });
    }
};
