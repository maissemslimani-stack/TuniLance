<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Freelancer's work submission for a given milestone
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();             // Submission description from freelancer
            $table->string('external_link')->nullable();   // Optional live demo / repository URL
            $table->string('status')->default('submitted'); // DeliverableStatus enum
            $table->text('rejection_reason')->nullable();  // Populated when client rejects
            $table->timestamps();

            $table->index(['milestone_id', 'status']);
        });

        // Each deliverable can have multiple file attachments
        Schema::create('deliverable_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverable_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');                          // Storage path
            $table->string('file_name');                          // Original file name for display
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();  // In bytes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_files');
        Schema::dropIfExists('deliverables');
    }
};
