<?php
// database/migrations/2026_07_30_000130_rename_proposals_to_submissions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('proposals', 'submissions');

        Schema::table('contracts', function (Blueprint $table) {
            $table->renameColumn('proposal_id', 'submission_id');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->renameColumn('submission_id', 'proposal_id');
        });

        Schema::rename('submissions', 'proposals');
    }
};