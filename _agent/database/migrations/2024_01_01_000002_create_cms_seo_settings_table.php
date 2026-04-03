<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('agent_username');
            $table->string('page_type');
            $table->string('page_identifier')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index, follow');
            $table->json('schema_markup')->nullable();
            $table->timestamps();

            $table->index('agent_username');
            $table->index(['page_type', 'page_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_seo_settings');
    }
};
