<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_applications', function (Blueprint $table): void {
            $table->json('documents')->nullable()->after('resume_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('career_applications', function (Blueprint $table): void {
            $table->dropColumn('documents');
        });
    }
};
