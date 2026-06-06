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
        Schema::create('cloudflare_settings', function (Blueprint $table) {
            $table->id();

            $table->string('account_id');
            $table->string('tunnel_id');

            $table->longText('api_token');

            $table->string('tunnel_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_settings');
    }
};
