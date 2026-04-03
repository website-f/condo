<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_social_posts', function (Blueprint $table) {
            $table->id();
            $table->string('agent_username');
            $table->unsignedBigInteger('social_account_id')->nullable();
            $table->text('content');
            $table->string('media_url')->nullable();
            $table->string('platform');
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('external_post_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('agent_username');
            $table->index('status');
            $table->index('scheduled_at');
            $table->foreign('social_account_id')->references('id')->on('cms_social_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_social_posts');
    }
};
