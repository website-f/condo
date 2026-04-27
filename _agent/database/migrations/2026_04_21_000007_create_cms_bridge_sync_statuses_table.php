<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_bridge_sync_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('agent_username')->nullable();
            $table->string('resource_type', 80);
            $table->string('resource_key', 191);
            $table->string('sync_target', 80);
            $table->string('last_operation', 80)->nullable();
            $table->string('sync_status', 30);
            $table->text('last_message')->nullable();
            $table->text('last_error')->nullable();
            $table->json('last_context')->nullable();
            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['resource_type', 'resource_key', 'sync_target'], 'cms_bridge_sync_statuses_resource_target_unique');
            $table->index(['agent_username', 'sync_status'], 'cms_bridge_sync_statuses_agent_status_idx');
            $table->index(['resource_type', 'resource_key'], 'cms_bridge_sync_statuses_resource_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_bridge_sync_statuses');
    }
};
