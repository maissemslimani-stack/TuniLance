<?php
// database/migrations/2026_08_10_000170_add_predefined_skills_and_categories.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('user_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'skill_id']);
        });

        Schema::create('project_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'skill_id']);
        });

        Schema::create('freelancer_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'category_id']);
        });

        // Old free-text columns — superseded by the predefined tables above.
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('skills');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('required_skills');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('required_skills')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('skills')->nullable();
        });

        Schema::dropIfExists('freelancer_categories');
        Schema::dropIfExists('project_skill');
        Schema::dropIfExists('user_skill');
        Schema::dropIfExists('skills');
    }
};