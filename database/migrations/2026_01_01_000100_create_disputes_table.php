<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete(); // Dispute may target a specific milestone
            $table->foreignId('reported_by_id')->constrained('users'); // Client or Freelancer who filed it
            $table->foreignId('moderator_id')->nullable()->constrained('users')->nullOnDelete(); // Assigned moderator
            $table->text('reason');                         // Summary from the reporter
            $table->string('status')->default('open');      // DisputeStatus enum
            $table->text('resolution_notes')->nullable();   // Moderator's written decision
            $table->string('resolution_outcome')->nullable(); // 'release_to_freelancer' | 'refund_to_client' | 'split'
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'status']);
            $table->index(['moderator_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
