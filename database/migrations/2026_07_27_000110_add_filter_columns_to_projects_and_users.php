<?php
// database/migrations/2026_07_27_000110_add_filter_columns_to_projects_and_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('experience_level')->nullable()->after('required_skills');
            $table->string('duration')->nullable()->after('experience_level');
        });

        Schema::table('users', function (Blueprint $table) {
            // Client verification — checked by a Moderator (company + card/bank on file)
            $table->boolean('is_verified')->default(false)->after('skills');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                ->constrained('users')->nullOnDelete(); // the moderator who verified them
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['experience_level', 'duration']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['is_verified', 'verified_at']);
        });
    }
};