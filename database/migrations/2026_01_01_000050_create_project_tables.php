<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->decimal('budget_min', 12, 2)->nullable(); // Client's minimum budget in TND
            $table->decimal('budget_max', 12, 2)->nullable(); // Client's maximum budget in TND
            $table->string('status')->default('draft');       // ProjectStatus enum
            $table->date('deadline')->nullable();             // Expected project completion date
            $table->json('required_skills')->nullable();      // Skills the client expects
            $table->unsignedInteger('proposals_count')->default(0); // Cached counter
            $table->timestamp('published_at')->nullable();    // When status changed to 'open'
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index(['category_id', 'status']);
        });

        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('bid_amount', 12, 2);      // Freelancer's proposed total price in TND
            $table->text('cover_letter');
            $table->unsignedInteger('delivery_days');  // Proposed delivery time in days
            $table->string('status')->default('pending'); // ProposalStatus enum
            $table->timestamps();
            $table->softDeletes();

            // A freelancer can only submit one proposal per project
            $table->unique(['project_id', 'freelancer_id']);
            $table->index(['freelancer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('projects');
    }
};
