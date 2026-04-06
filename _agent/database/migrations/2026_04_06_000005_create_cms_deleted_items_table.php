<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_deleted_items', function (Blueprint $table) {
            $table->id();
            $table->string('agent_username')->nullable();
            $table->string('entity_group', 50);
            $table->string('entity_type', 80);
            $table->string('entity_key', 191);
            $table->string('source_key', 50)->nullable();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('payload')->nullable();
            $table->dateTime('deleted_at');
            $table->timestamps();

            $table->unique(['entity_type', 'entity_key'], 'cms_deleted_items_entity_unique');
            $table->index(['agent_username', 'entity_group', 'deleted_at'], 'cms_deleted_items_agent_group_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_deleted_items');
    }
};
