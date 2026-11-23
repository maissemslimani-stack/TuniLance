<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete(); // The winning proposal
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('freelancer_id')->constrained('users');
            $table->foreignId('moderator_id')->nullable()->constrained('users')->nullOnDelete(); // Assigned on dispute

            $table->string('status')->default('active'); // ContractStatus enum
            $table->decimal('total_amount', 12, 2);     // Agreed contract value in TND (from proposal bid)
            $table->text('terms')->nullable();           // Optional contract terms/notes
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index(['freelancer_id', 'status']);
        });

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);    // Milestone value in TND
            $table->string('status')->default('pending'); // MilestoneStatus enum
            $table->date('due_date')->nullable();
            $table->integer('sort_order')->default(0); // Display order within a contract
            $table->timestamps();

            $table->index(['contract_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('contracts');
    }
};
