<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Extend the default users table with Tunilance-specific columns
            $table->string('role')->default('client')->after('email'); // 'client' | 'freelancer' | 'moderator'
            $table->text('bio')->nullable()->after('role');
            $table->json('skills')->nullable()->after('bio'); // Array of skill strings e.g. ["PHP", "Vue.js"]
            $table->string('avatar_url')->nullable()->after('skills');
            $table->string('headline')->nullable()->after('avatar_url'); // Short professional tagline
            $table->boolean('is_active')->default(true)->after('headline');
            $table->timestamp('last_seen_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'bio', 'skills', 'avatar_url', 'headline', 'is_active', 'last_seen_at']);
        });
    }
};
