<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condo_agent_domains', function (Blueprint $table) {
            $table->id();
            $table->string('agent_username');
            $table->string('host')->unique();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('ssl_status')->nullable();
            $table->timestamps();

            $table->index(['agent_username', 'is_active']);
            $table->index(['agent_username', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condo_agent_domains');
    }
};
