<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── In-chat proposal negotiation ──────────────────────────────────
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_proposal')->default(false)->after('is_system_message');
            $table->string('proposal_status')->nullable()->after('is_proposal'); // pending | accepted | rejected
            $table->foreignId('proposal_resolved_by')->nullable()->after('proposal_status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('proposal_resolved_at')->nullable()->after('proposal_resolved_by');
        });

        Schema::table('chat_rooms', function (Blueprint $table) {
            // The single "live" proposal in this room — new @proposal messages overwrite this,
            // which is what makes older pending proposals go stale/non-actionable.
            $table->foreignId('active_proposal_message_id')->nullable()->after('last_message_at')
                ->constrained('chat_messages')->nullOnDelete();
        });

        // ─── Contract early-termination tracking ───────────────────────────
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('ended_by')->nullable()->after('completed_at')
                ->constrained('users')->nullOnDelete();
            $table->text('end_reason')->nullable()->after('ended_by');
            $table->boolean('reported_to_moderator')->default(false)->after('end_reason');
        });

        // ─── Per-project freelancer blocklist ───────────────────────────────
        Schema::create('project_freelancer_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason')->nullable(); // 'changed' | 'dropped'
            $table->timestamps();

            $table->unique(['project_id', 'freelancer_id']);
        });

        // ─── Reviews ─────────────────────────────────────────────────────────
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'reviewer_id']); // one review per person per contract
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('project_freelancer_blocks');

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ended_by');
            $table->dropColumn(['end_reason', 'reported_to_moderator']);
        });

        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_proposal_message_id');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proposal_resolved_by');
            $table->dropColumn(['is_proposal', 'proposal_status', 'proposal_resolved_at']);
        });
    }
};