<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Must be a freelancer
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable(); // Main preview image
            $table->string('project_link')->nullable();  // External URL to the live project
            $table->boolean('is_featured')->default(false); // Pin item to top of portfolio
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('portfolio_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_item_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // In bytes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_attachments');
        Schema::dropIfExists('portfolio_items');
    }
};
